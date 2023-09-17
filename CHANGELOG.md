# Histórico de cambios
---
Todos los cambios notables de este proyecto se documentarán en este archivo.

* ## [Sin versión]
  > Ver TODO.md

---
* ## [v2.0.3] - 2023-09-17
  > Mejoras.

  * #### Cambios:
    - Refactor del formulario de configuración del módulo.

  * ### Eliminados:
    - Nombre del paquete, no es necesario y molesta a la hora de buscar
      actualizaciones.

---
* ## [v2.0.2] - 2023-05-19
  > Mejoras.

  * #### Añadido:
    - Funcionalidad para eliminar pequeños rastros de configuraciones al
      desinstalar el módulo.

  * #### Cambios:
    - Mejora de la documentación del repositorio.

  * #### Corrección de errores
    - Adaptación de algunas llamadas a funciones y pasarlas a servicios.
    - Ajustes en los menús que genera el módulo por problemas con las rutas.

---
* ## [v2.0.1] - 2022-11-30
  > Nuevas funcionalidades.

  * #### Añadido:
    - Compatibilidad con Drupal 10.
    - Mejora de la documentación.

  * #### Corrección de errores:
    - Error al mostrar la ayuda del módulo si no existe el archivo README.md.

---
* ## [v2.0.0] - 2022-04-25
  > Nuevas funcionalidades.

  * #### Añadido:
    - Plantillas para TODO.md, CHANGELOG.md y README.md
    - CSS para la visualización en SettingsForm de los archivos anteriores.
    - LICENSE.md y CODE_OF_CONDUCT.md
    - Plantilla para composer.json.
    - Nueva estructura de menús para todos los módulos custom.

  * ### Eliminados:
    - Se elimina el script de instalación en favor del script iniciar-proyecto.
      Esto se debe a la dificultad de mantener tantos scripts actualizados.

  * #### Corrección de errores:
    - Error en la llamada a la librería para markdown en los templates.
    - Error en la estructura del formulario de configuración.
    - Error al establecer el permiso de configuración del módulo.

---
* ## [v1.0.0] - 2021-03-17
  > Primera versión (no publicada).
