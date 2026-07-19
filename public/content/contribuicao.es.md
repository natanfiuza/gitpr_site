# Contribuyendo a GitPR CLI

¡Las contribuciones son muy bienvenidas! Aquí te mostramos cómo participar.

---

## Cómo Contribuir

1. **Haz un Fork** del proyecto en [GitHub](https://github.com/natafiuza/gitpr)
2. **Crea una rama** para tu funcionalidad:
   ```bash
   git checkout -b feature/NuevaFuncionalidad
   ```
3. **Haz commit de tus cambios:**
   ```bash
   git commit -m 'feat: añadir nueva funcionalidad'
   ```
   > 💡 **Consejo:** ¡Usa el propio GitPR para generar este mensaje de commit! Solo ejecuta `gitpr -c`.

4. **Empuja** a tu rama:
   ```bash
   git push origin feature/NuevaFuncionalidad
   ```
5. **Abre un Pull Request** en el repositorio principal

---

## Configuración de Desarrollo

```bash
# Clonar y entrar
git clone https://github.com/natafiuza/gitpr.git
cd gitpr

# Instalar todas las dependencias (incluyendo dev)
pipenv install --dev

# Ejecutar pruebas
pipenv run pytest -v

# Ejecutar GitPR desde el código fuente
pipenv run python src/main.py
```

---

## Ejecutando Pruebas

```bash
# Ejecutar todas las pruebas con salida detallada
pipenv run pytest -v

# Ejecutar un archivo de prueba específico
pipenv run pytest tests/test_core.py -v

# Ejecutar con informe de cobertura
pipenv run pytest --cov=src --cov-report=term-missing
```

---

## Áreas para Contribuir

| Área | Descripción |
| --- | --- |
| **Nuevos Proveedores** | Añadir soporte para proveedores IA adicionales (Claude, LLMs locales, etc.) |
| **Nuevos Idiomas** | Traducir GitPR a tu idioma |
| **Reglas de Linter** | Compartir conjuntos útiles de reglas para diferentes stacks |
| **Documentación** | Mejorar la documentación, añadir ejemplos, corregir errores |
| **Corrección de Bugs** | Consultar la pestaña de issues para bugs reportados |
| **Mejoras en la TUI** | Mejorar la interfaz del chat interactivo y editor de issues |

---

## Estructura del Proyecto

```
gitpr/
├── src/
│   ├── main.py           # Punto de entrada CLI y enrutamiento de comandos
│   ├── core.py            # Operaciones Git e integración IA
│   ├── config.py          # Gestión de configuración y .env
│   ├── security.py        # Cifrado (Fernet)
│   ├── linter_engine.py   # Motor de análisis estático
│   ├── updater.py         # Auto-updater (hot-swap)
│   └── i18n.py            # Helper de internacionalización
├── tests/                 # Pruebas unitarias y de integración
├── langs/                 # Archivos de traducción (JSON)
├── docs/                  # Documentación extendida
└── run.py                 # Punto de entrada PyInstaller
```

---

## Publicando en PyPI

Para mantenedores:

```bash
pipenv run python -m build
pipenv run twine upload dist/*
```

---

## Licencia

Este proyecto está bajo la licencia **GNU Lesser General Public License v2.1 (LGPL-2.1)**.

Consulta el archivo [LICENSE](https://github.com/natafiuza/gitpr/blob/main/LICENSE) para más detalles.

---

## Agradecimientos

Proyecto concebido y desarrollado por **Natan Fiuza** — [contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br)

---

[← Caché y Actualizaciones](/cache)
