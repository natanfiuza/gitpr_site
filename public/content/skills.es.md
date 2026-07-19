# Sistema de Skills — Ingeniería de Prompt

GitPR usa **plantillas Markdown personalizables** como instrucciones de sistema de la IA. En lugar de codificar prompts, controlas la "persona" de la IA a través de archivos locales `.gitpr.*.md` — adaptados a las convenciones y reglas de negocio de tu equipo.

---

## Generando Plantillas

```bash
gitpr -s
# o
gitpr --skill
```

Esto crea los siguientes archivos en la raíz de tu proyecto:

---

## Archivos de Plantilla

### `.gitpr.commit.md`
Reglas para generar mensajes de commit. Define tu formato preferido, tono y convenciones.

```markdown
# Ejemplo de personalización
- Usa el formato Conventional Commits
- Máximo 72 caracteres para la línea de resumen
- Incluye scope cuando corresponda: feat(scope): descripción
- Usa modo imperativo ("añade" no "añadido")
```

---

### `.gitpr.pr.md`
Estructura requerida para descripciones de Pull Request. Define secciones, nivel de detalle y formato.

```markdown
# Ejemplo de personalización
Tu descripción de PR debe incluir:
1. **Resumen** — un párrafo describiendo el cambio
2. **Motivación** — por qué este cambio es necesario
3. **Pruebas** — cómo se probó el cambio
4. **Capturas de pantalla** — si hay cambios de UI
5. **Breaking Changes** — lista cualquier cambio incompatible
```

---

### `.gitpr.review.md`
Enfoque arquitectónico para análisis de diff. Define qué debe priorizar la IA durante los code reviews.

```markdown
# Ejemplo de personalización
Enfoca tu revisión en:
- Violaciones de los principios SOLID
- Vulnerabilidades de seguridad (OWASP Top 10)
- Cuellos de botella de rendimiento
- Brechas en el manejo de errores
- Adecuación de la cobertura de pruebas
```

---

### `.gitpr.filereview.md`
Reglas estrictas de cohesión y acoplamiento para auditoría completa de archivos (usado con `--input`).

```markdown
# Ejemplo de personalización
Analiza este archivo en busca de:
- Violaciones del Principio de Responsabilidad Única
- Acoplamiento fuerte con servicios externos
- Falta de inyección de dependencia
- Números mágicos y valores hardcodeados
- Funciones de más de 30 líneas
```

---

### `.gitpr.issue.md`
Estructura y nivel de detalle para generación estandarizada de issues (usado con `--issue`).

```markdown
# Ejemplo de personalización
Las issues deben contener:
1. **Descripción** — declaración clara del problema
2. **Pasos para Reproducir** — lista numerada
3. **Comportamiento Esperado**
4. **Comportamiento Actual**
5. **Entorno** — SO, versión, etc.
6. **Criterios de Aceptación** — formato checklist
```

---

### `.gitpr.blame.md`
Enfoque para análisis arqueológico al rastrear evolución de código legacy (usado con `--blame`).

```markdown
# Ejemplo de personalización
Al rastrear historial de código, identifica:
- Commit y autor original
- Por qué se tomó la decisión (por los mensajes de commit)
- Enfoques alternativos considerados
- Relevancia actual de las restricciones originales
```

---

## Cómo Funciona

1. **Plantillas específicas del proyecto** — cada repositorio puede tener sus propias convenciones
2. **La IA las lee como instrucciones del sistema** — se anteponen a cada prompt relevante
3. **Versionadas** — commitéalas en tu repo para que todo el equipo comparta los mismos estándares
4. **Personalización sin código** — sin necesidad de modificar el código fuente de GitPR

---

## Ejemplo de Flujo

```bash
# 1. Generar plantillas
gitpr -s

# 2. Personalizarlas para tu equipo
vim .gitpr.commit.md
vim .gitpr.review.md

# 3. Commitearlas en el repositorio
git add .gitpr.*.md
git commit -m "feat: añadir plantillas de skill GitPR personalizadas"

# 4. Cada miembro del equipo ahora recibe el mismo comportamiento IA
```

---

[← Proveedores IA](/providers) &nbsp;|&nbsp; [Internacionalización →](/i18n)
