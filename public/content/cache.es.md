# Sistema de Caché y Auto-Updater

GitPR incluye caché inteligente para ahorrar cuotas de API y un auto-updater transparente para mantenerte en la última versión.

---

## ⚡ Sistema de Caché Local

Cada vez que ejecutas un comando impulsado por IA (`--review`, `--commit`, etc.), GitPR genera un **hash MD5** de tu código actual (diff) combinado con tus instrucciones.

Si ejecutas el **mismo comando** sin modificar el código, GitPR intercepta la solicitud y devuelve el resultado **instantáneamente desde la caché** — sin llamada API, sin gasto de cuota.

### Cómo Funciona

1. El comando se ejecuta → diff + instrucciones son hasheados (MD5)
2. Si el hash existe en `~/.gitpr/cache/prompts/` → devuelve resultado cacheado
3. Si no → llama a la IA, guarda la respuesta, devuelve el resultado

### Beneficios

- **Cero llamadas API duplicadas** — re-ejecutar la misma revisión no cuesta nada
- **Respuestas en milisegundos** — las lecturas de caché son instantáneas
- **Invalidación automática** — cualquier cambio en el código produce un hash diferente
- **Transparente** — sin necesidad de flags, siempre activo

---

## 🔄 Auto-Updater (Actualización OTA)

GitPR verifica actualizaciones silenciosamente en cada ejecución y puede hacer hot-swap del binario en segundos.

### Verificar y Actualizar

```bash
# Forzar verificación de actualización
gitpr -u
# o
gitpr --update
```

### Cómo Funciona

1. **Guardián de Conexión:** Verifica la disponibilidad de red antes de iniciar — nunca bloquea flujos de trabajo sin conexión
2. **Verificación silenciosa en segundo plano:** En cada ejecución, compara la versión local con la última Release de GitHub
3. **Técnica Hot-Swap:** Descarga el nuevo binario, renombra el antiguo como respaldo y lo reemplaza de forma transparente — mientras la ejecución actual termina normalmente
4. **Capacidad de rollback:** Si la nueva versión falla, el binario antiguo sigue en disco

### Verificación de Versión

GitPR usa **checksums SHA-256** publicados con cada Release de GitHub para verificar la integridad del binario antes de la instalación.

---

## Flujo Combinado

```bash
# 1. Trabaja normalmente — la caché te evita llamadas API duplicadas
gitpr -r
gitpr -r  # Mismo diff → cache hit instantáneo ⚡

# 2. Modifica algún código → nuevo hash → nueva llamada IA
# ... editar archivos ...
gitpr -r  # Diff diferente → nuevo análisis

# 3. Mantente actualizado sin esfuerzo
gitpr -u  # Verificar e instalar la última versión
```

---

## Almacenamiento de la Caché

Todos los archivos de caché se encuentran en `~/.gitpr/cache/prompts/`. Puedes eliminar este directorio de forma segura para liberar espacio en disco — GitPR lo recreará según sea necesario.

```bash
# Limpiar todas las respuestas cacheadas
rm -rf ~/.gitpr/cache/prompts/
```

---

[← Internacionalización](/i18n) &nbsp;|&nbsp; [Contribución →](/contribuicao)
