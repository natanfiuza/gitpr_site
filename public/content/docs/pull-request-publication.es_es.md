# Documentación Técnica: Publicación de Pull Requests en GitHub (`--publish`)

Esta documentación describe el flujo de publicación de Pull Requests a través de la interfaz de terminal interactiva (TUI), que te permite revisar, editar y publicar Pull Requests directamente en GitHub sin salir de la terminal.

---

## 1. ¿Qué es el Publicador de PR?

Cuando ejecutas el comando `gitpr --publish`, GitPR genera la descripción del PR con IA (igual que el comando predeterminado), guarda el archivo `.md` localmente y abre un panel interactivo directamente en la terminal. Esto te permite revisar, editar y publicar la Pull Request generada por la Inteligencia Artificial antes de enviarla al repositorio remoto a través de la API REST.

---

## 2. Modos de publicación

El Publicador de PR tiene **3 modos de ejecución**, activados según las banderas combinadas con `--publish`.

### 2.1 Modo interactivo — `gitpr --publish`

Abre la TUI para revisión y edición antes de publicar.

```bash
gitpr --publish
```

| Característica | Descripción |
|---|---|
| **Flujo** | `git fetch` → la IA genera el PR → `.md` guardado → se abre la TUI → el usuario edita → POST a GitHub |
| **Cuándo usarlo** | Cuando quieres revisar y ajustar el contenido del PR antes de publicarlo |
| **Resultado** | Pull Request creada en GitHub con el contenido editado |
| **Ideal para** | Flujo de trabajo estándar: control total sobre lo que se publica |

> **Consejo:** El archivo `.md` local se guarda antes de que se abra la TUI y se vuelve a guardar con las ediciones realizadas antes de publicar. Siempre tienes una copia de seguridad.

---

### 2.2 Publicación directa — `gitpr --publish --no-edit`

Omite el editor interactivo y publica directamente.

```bash
gitpr --publish --no-edit
```

| Característica | Descripción |
|---|---|
| **Flujo** | `git fetch` → la IA genera el PR → `.md` guardado → POST directo a GitHub |
| **Cuándo usarlo** | Cuando confías en la salida de la IA y quieres publicar de inmediato |
| **Resultado** | Pull Request creada en GitHub sin abrir la TUI |
| **Ideal para** | Pipelines de CI/CD, correcciones rápidas, flujos de trabajo automatizados |

> **Precaución:** Úsalo con cuidado: no tendrás oportunidad de revisar ni editar el contenido antes de publicar.

---

### 2.3 Modo de publicación automática — `PR_AUTO_PUBLISH=true`

Configura GitPR para que siempre abra la TUI del publicador después de generar una descripción de PR.

```bash
# En ~/.gitpr/.env
PR_AUTO_PUBLISH=true
```

| Característica | Descripción |
|---|---|
| **Activación** | Variable de entorno en `~/.gitpr/.env` |
| **Comportamiento** | Cada ejecución de `gitpr` abre la TUI del publicador después de generar el PR |
| **Cuándo usarlo** | Cuando siempre quieres publicar después de generar la descripción del PR |
| **Ideal para** | Equipos que siguen un flujo de trabajo de "generar y publicar" |

---

## 3. Configuración de la rama base

La rama de destino de la Pull Request se resuelve en el siguiente orden de prioridad:

| Prioridad | Origen | Cómo configurarla |
|---|---|---|
| **1 (más alta)** | Bandera `--base` | `gitpr --publish --base develop` |
| **2** | Variable de entorno `PR_DEFAULT_BASE` | `PR_DEFAULT_BASE=develop` en `~/.gitpr/.env` |
| **3 (predeterminada)** | Detección automática | `git symbolic-ref refs/remotes/origin/HEAD` (normalmente `main` o `master`) |

---

## 4. Atajos y navegación en la TUI

La interfaz fue diseñada para ser rápida y no requerir un uso constante del ratón. Puedes navegar por los campos con la tecla `Tab` y usar los siguientes atajos:

| Tecla | Acción | Descripción |
|---|---|---|
| **`F1`** | Ayuda | Abre un modal flotante con instrucciones rápidas de uso de la interfaz |
| **`F2`** | Guardar `.md` local | Guarda el contenido actualizado en el archivo de descripción del PR del proyecto actual. Ideal cuando quieres perfeccionar el contenido más tarde |
| **`F3`** | Publicar PR | Se conecta a la API REST de GitHub y crea la Pull Request en el repositorio remoto. El enlace directo al PR recién creado se mostrará en la terminal |
| **`Esc`** | Salir | Aborta la operación y cierra la interfaz sin publicar |
| **`Tab`** | Navegar | Alterna el foco entre los campos de la interfaz |

---

## 5. Integración con GitHub (token PAT)

Para crear Pull Requests directamente en el repositorio remoto (`F3`), GitPR necesita un **token de acceso personal (PAT)** de GitHub con el ámbito `repo`.

### 5.1 Configuración del token

La primera vez que uses `F3`, GitPR:

1. Detectará que no hay ningún token configurado
2. Mostrará la URL de generación del token con parámetros pre-rellenados (ámbito `repo`)
3. Te pedirá que pegues el token generado
4. Lo guardará cifrado (Fernet) en el archivo `~/.gitpr/.env`

> **Nota:** La TUI de Issues (`gitpr -is`) comparte el mismo token. Si ya configuraste un token para Issues, se reutilizará automáticamente.

### 5.2 Seguridad

- El token se almacena como un hash cifrado — nunca en texto plano
- La clave maestra de descifrado se encuentra en `~/.gitpr/secret.key`
- El token se valida mediante `GET /user` antes de que se abra la TUI
- Consulta la guía completa en [github-pat-integration.md](github-pat-integration.md)

---

## 6. API de GitHub — creación de PR

El PR se crea mediante `POST https://api.github.com/repos/{owner}/{repo}/pulls` con la siguiente carga útil:

```json
{
  "title": "PR title (editable in TUI)",
  "body": "Full markdown PR description with commit message",
  "head": "Current branch (source)",
  "base": "Target branch (main, develop, etc.)"
}
```

---

## 7. Manejo de errores

| Error | Comportamiento |
|---|---|
| Token no válido o caducado (401) | Solicita un nuevo token (hasta 3 intentos) |
| Rama no encontrada (422) | Muestra el mensaje de error de GitHub con detalles |
| Sin commits para fusionar (422) | Muestra un error de validación que sugiere hacer cambios primero |
| El PR ya existe (422) | Muestra el conflicto específico |
| Fallo de red | Muestra el mensaje de error de conexión |
| Remoto ausente | Error antes de que se abra la TUI: no se intenta ninguna llamada a la API |

---

## 8. Variables de entorno

| Variable | Predeterminado | Descripción |
|---|---|---|
| `GITHUB_TOKEN_ENCRYPTED` | *(ninguno)* | Token de acceso personal de GitHub cifrado |
| `PR_DEFAULT_BASE` | *(vacío)* | Rama de destino predeterminada (usa la detección automática cuando está vacío) |
| `PR_AUTO_PUBLISH` | `false` | Establécelo en `true` para abrir siempre el publicador después de la generación del PR |

---

## 9. Ejemplos prácticos

### Ejemplo 1: Revisar y publicar una funcionalidad

```bash
# Terminaste el desarrollo en la rama feature/login
gitpr --publish
# → La IA genera la descripción del PR y abre la TUI
# → Revisa el título, el cuerpo y la rama base
# → Pulsa F3 para crear el PR en GitHub
```

### Ejemplo 2: Publicación rápida sin editar

```bash
gitpr --publish --no-edit
# → El PR se genera y se publica de inmediato
# → La URL del PR se muestra en la terminal
```

### Ejemplo 3: Publicar contra una rama base personalizada

```bash
gitpr --publish --base staging
# → La rama de destino se establece en "staging" en lugar de "main"
```

---

## 10. Archivos relacionados

| Archivo | Función |
|---|---|
| `.gitpr.pr.md` | Plantilla local con reglas personalizadas para la generación de la descripción del PR (descárgala con `gitpr -s`) |
| `~/.gitpr/.env` | Configuración global: claves de API, valores predeterminados del PR y token de GitHub cifrado |
| `~/.gitpr/secret.key` | Clave maestra de Fernet para el descifrado de credenciales |

> **Nota:** Consulta también la [documentación principal (README.md)](../README.md) para obtener una visión general de todas las funciones de GitPR y la [guía de descripción de PR](pr-descricao-padrao.md) para el flujo predeterminado de generación de PR.
