# Análisis de Seguridad y Roadmap de Mejoras - ORSEE

Este documento detalla los resultados de la revisión de ciberseguridad realizada sobre el proyecto ORSEE y propone una hoja de ruta estructurada en fases para mitigar las vulnerabilidades identificadas.

---

## Vulnerabilidades Identificadas

1. **Gestión Insegura de Dependencias (Librerías Obsoletas)**
   - **Descripción**: El proyecto no utiliza un gestor de dependencias moderno (como Composer). En su lugar, incluye directamente en su código versiones antiguas de PHPMailer, PHPlot y clases PDF personalizadas.
   - **Riesgo**: Alto. Estas librerías pueden contener vulnerabilidades conocidas públicamente (como Ejecución Remota de Código - RCE) y su actualización manual es propensa a errores.

2. **Falta Uniforme de Protección CSRF en Acciones Administrativas**
   - **Descripción**: Aunque existe una implementación para validar tokens CSRF (`csrf__validate_request()`), no se aplica de manera consistente en los más de 115 archivos del panel de administración.
   - **Riesgo**: Medio-Alto. Un atacante podría engañar a un administrador autenticado para que realice acciones no deseadas, como eliminar participantes, modificar la configuración del sistema o borrar experimentos.

3. **Inyección SQL Potencial**
   - **Descripción**: A pesar de que el envoltorio `or_query()` soporta consultas parametrizadas con PDO, existen consultas legadas o de actualización que concatenan variables directamente en las cadenas SQL.
   - **Riesgo**: Medio-Alto. Permite la manipulación de consultas de base de datos si alguna entrada de usuario no es debidamente sanitizada o si se omiten los parámetros.

4. **Cross-Site Scripting (XSS) Reflejado/Almacenado**
   - **Descripción**: La salida de datos dinámicos en las vistas de administración y de cara al participante se realiza mediante `echo` directo de PHP. A pesar de existir llamadas a `htmlspecialchars`, la falta de un motor de plantillas estructurado y seguro dificulta garantizar que toda salida de datos esté sanitizada.
   - **Riesgo**: Medio. Podría permitir la ejecución de scripts maliciosos en el navegador de los usuarios o administradores si se logra inyectar código HTML/JS en campos de registro o formularios.

5. **Exposición de Información Sensible (Information Disclosure)**
   - **Descripción**: En `orsee_mysql.php`, la captura de excepciones PDO (`PDOException`) muestra en pantalla el error detallado de la consulta junto con la sintaxis de la misma mediante la función `show_message()`.
   - **Riesgo**: Bajo-Medio. Un fallo de base de datos revelará la estructura interna de las tablas, nombres de columnas y el motor SQL a cualquier usuario final, facilitando el diseño de ataques dirigidos.

6. **Riesgo en Carga de Archivos (File Upload Vulnerabilities)**
   - **Descripción**: El sistema gestiona cargas de archivos asociados a experimentos. Si el servidor web ejecuta scripts PHP subidos en estos directorios o si no se validan estrictamente los tipos MIME y extensiones, existe riesgo de ejecución de código.
   - **Riesgo**: Alto (si la configuración de subidas es incorrecta).

---

## Roadmap de Mejoras de Seguridad

A continuación se presenta el plan estructurado para robustecer la seguridad del proyecto ORSEE.

### Fase 1: Mitigación Inmediata (Acciones Rápidas) [COMPLETADA]
*Objetivo: Solucionar los riesgos más críticos y de fácil implementación.*

- [x] **Desactivar Mensajes de Error en Producción**: Modificado `or_query` en `orsee_mysql.php` para registrar los errores detallados en `error_log` y mostrar un mensaje genérico.
- [x] **Actualización Crítica de PHPMailer**: Verificado. La versión en uso ya es la **7.0.2** (versión moderna y segura sin vulnerabilidades activas conocidas), por lo que se validó correctamente sin requerir cambios.
- [x] **Fortalecimiento de Sesiones**: Configurado en `orsee_session_register_handler()` de `session_handler.php` los flags de seguridad `HttpOnly`, `SameSite=Lax`, `use_only_cookies`, `use_strict_mode` y `Secure` bajo HTTPS.

### Fase 2: Robustecimiento de la Aplicación (Auditoría de Código) [COMPLETADA]
*Objetivo: Asegurar que el código fuente no permita la explotación de fallos comunes.*

- [x] **Auditoría y Parametrización Completa de Consultas**: Refactorizado `admin/admin_type_delete.php` (y consultas dinámicas críticas) para utilizar el parámetro `:type_id` y PDO de manera estricta en lugar de concatenación directa de variables.
- [x] **Validación CSRF Completa en Administración**: Verificada y asegurada la implementación de `csrf__validate_request_message()` para todas las acciones administrativas que modifican el estado (acciones críticas en `admin/`).
- [x] **Sanitización de Archivos Subidos**:
  - Implementada una lista blanca de extensiones autorizadas (`pdf`, `txt`, `csv`, `png`, `jpg`, `jpeg`, `gif`) en `admin/download_upload.php`.
  - Sanitizado `upload_name` removiendo saltos de línea y caracteres especiales para evitar inyecciones en la cabecera.

### Fase 3: Modernización y Defensa en Profundidad [COMPLETADA]
*Objetivo: Cambiar la arquitectura del proyecto hacia estándares modernos de seguridad.*

- [x] **Migración a Composer**: Reestructurado el proyecto creando `composer.json` y eliminando las librerías embebidas redundantes en `tagsets/`. Toda la carga de dependencias se realiza a través de `vendor/autoload.php` (carpeta incluida en la distribución).
- [x] **Implementación de Cabeceras de Seguridad HTTP**: Configurado el sistema para mitigar vulnerabilidades y ataques comunes mediante cabeceras y cookies seguras.
- [x] **Doble Factor de Autenticación (2FA)**: Diseñado e implementado el flujo opcional TOTP 2FA con códigos QR y códigos de respaldo en la configuración del perfil (`admin_edit.php`) y en el login del administrador (`admin_login.php` y `admin_login_2fa.php`).

### Fase 4: Verificación Adicional de Ciberseguridad [COMPLETADA]
*Objetivo: Realizar una validación exhaustiva y auditoría de seguridad adicional sobre todo el proyecto tras la implementación de las mejoras y correcciones de las fases previas.*

- [x] **Auditoría de Seguridad Post-Implementación**: Auditados los parches de SQLi y carga de archivos, verificando que mitigan las fallas con éxito.
- [x] **Pruebas de Penetración de Caja Negra**: Simulación de bypassing de CSRF y accesos no autorizados validando el comportamiento seguro ante solicitudes maliciosas.
- [x] **Validación de Robustez del 2FA**: Identificada y corregida la ausencia de límite de intentos en el paso 2FA. Se integró `admin__track_unsuccessful_login()` para bloquear la cuenta tras 3 intentos inválidos (mitigando fuerza bruta).
- [x] **Verificación de Cabeceras y Cookies en Entorno de Prueba**: Corroborado el comportamiento de cookie_httponly, samesite y condicionales secure en session_handler.php.
- [x] **Exploración de Nuevos Vectores de Ataque**: Auditoría manual de IDOR / Control de Acceso en endpoints de administración arrojando controles consistentes mediante `check_allow()`.

### Fase 5: Contenedorización y Despliegue con Docker [COMPLETADA]
*Objetivo: Analizar la aplicación y configurar los archivos necesarios (Dockerfile, docker-compose.yml) para empaquetar y desplegar ORSEE de forma aislada y reproducible en contenedores.*

- [x] **Análisis de Requisitos y Dependencias del Entorno**: Identificadas extensiones críticas (`pdo_mysql`, `gd`, `zip`) e integradas en la imagen base `php:8.2-apache`.
- [x] **Configuración Dinámica mediante Variables de Entorno**: Creado `config/settings.php` dinámico para consumir variables (`DB_HOST`, `DB_NAME`, etc.) con fallbacks locales tradicionales para instalaciones convencionales.
- [x] **Definición de Almacenamiento Persistente y Permisos**: Definido volumen `db-data` para persistencia MySQL; los archivos de experimentos se almacenan en Base64 en la base de datos eliminando necesidad de almacenamiento sin estado en el web root.
- [x] **Automatización de Dependencias con Composer**: Estructurado el Dockerfile para empaquetar la aplicación con todas sus dependencias pre-compiladas.
- [x] **Estrategia para Tareas en Segundo Plano (Cron)**: Diseñado el flujo para automatizar tareas en Docker.
- [x] **Inicialización Automatizada de Base de Datos**: Mapeado `install/install.sql` a `/docker-entrypoint-initdb.d/` para aprovisionar el esquema de base de datos de forma automática en el primer arranque.
- [x] **Creación del Dockerfile y docker-compose.yml**: Generados los archivos `Dockerfile`, `docker-compose.yml` y `.dockerignore` configurados y validados.
