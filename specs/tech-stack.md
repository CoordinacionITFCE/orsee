# Stack Tecnológico de ORSEE

El proyecto está desarrollado utilizando una arquitectura web clásica monolítica (LAMP/LEMP).

## Tecnologías Principales

- **Lenguaje de Programación**: PHP (Soporta versiones desde 5.x hasta 8.x).
- **Base de Datos**: MySQL / MariaDB (Relacional, administrada mediante PDO en PHP).
- **Frontend**: HTML5, CSS3 vanilla (con layouts personalizados y adaptabilidad para móviles/tablets), JavaScript nativo (Vanilla JS).
- **Servidor Web**: Compatible con Apache y Nginx.

## Dependencias y Librerías Integradas (Embebidas)

A diferencia de los proyectos modernos que utilizan administradores de paquetes (como Composer), ORSEE incluye sus dependencias directamente dentro del directorio de código fuente (`tagsets/`):

- **PHPMailer**: Para la gestión y envío de correos electrónicos.
- **PHPlot**: Para la generación de gráficos estadísticos dentro de la plataforma.
- **EZPDF / PDF / FMailbox**: Clases personalizadas para la manipulación y exportación de archivos PDF y la lectura de bandejas de entrada de correo electrónico.
- **Bulma / Estilos Propios**: El diseño visual utiliza hojas de estilo CSS locales y clases personalizadas para interfaces web y componentes de formularios.
