![Repositorio](https://img.shields.io/github/repo-size/ScriptParaPHPost/ZCode?style=flat)
![PHP 8](https://img.shields.io/badge/PHP-8.4.0-teal?style=flat)
![Smarty 5+](https://img.shields.io/badge/Smarty-5.8.0-teal?style=flat)

# ZCode v4
ZCode v4 es una versión orientada a la modernización integral del núcleo del sistema. Se incorporaron mejoras arquitectónicas profundas, una estructura más limpia y escalable, mayor seguridad, separación clara de responsabilidades y un entorno preparado para desarrollo y mantenimiento a largo plazo.

---

## ✅ Novedades destacadas

- Estructura renovada con **namespaces**, **traits**, clases independientes y mayor organización interna.
- Nuevo módulo **Autenticar** para login/logout seguro y centralizado.
- Protección **CSRF** integrada en formularios críticos.
- Sistema de sesiones reorganizado: ahora gestionado mediante la clase `Session`, fuera de `User.php`.
- Reordenamiento de carpetas con **cumplimiento estricto del estándar PSR-4**.
- Integración de **PHP_CodeSniffer** para verificación del estándar PSR-12.
- Reordenamiento de carpetas, mayor legibilidad y separación lógica del código.

---

## 📚 Dependencias incluidas
ZCode v4 incorpora las librerías necesarias directamente en el proyecto:

- **Smarty 5.8.0** – motor de plantillas moderno y flexible.
- **PHP dotenv** – manejo seguro de configuración mediante `.env`.
- **Symfony Cache** – caché de alto rendimiento.
- **OTPHP** – Implementación de 2FA basada en TOTP/HOTP.
- **JBBCode** – parser BBCode con parches aplicados.
- **Psr/** — interfaces estándar para `psr/cache`, `psr/clock`, `psr/simple-cache`.

---

## 🛠️ Dependencias de desarrollo
Herramientas utilizadas durante el desarrollo, no requeridas en producción:

- **PHP_CodeSniffer** – verificación del estándar de código PSR-12.

---

## ⚙️ Requisitos
- PHP **8.3 o superior**
- **Composer**
- Extensiones recomendadas: `mbstring`, `openssl`, `json`, `pdo`

---

## 🚀 Instalación

```bash
composer install
```

### ¿No tenés Composer? Podés instalarlo aquí:
- Sitio oficial: https://getcomposer.org/download/
- Guía: https://kinsta.com/es/blog/instalar-composer/
- Guía para instalar PHP: https://kinsta.com/es/blog/instalar-php/

---

## 🔍 Herramientas de desarrollo

```bash
# Verificar estilo de código PSR-12
composer phpcs

# Corregir estilo automáticamente
composer phpcs:fix
```

---

## 📌 Estado del proyecto
ZCode v4 es la versión actual y continúa evolucionando con un enfoque en: `[ESTADO: EN DESARROLLO]`
- Arquitectura modular.
- Seguridad.
- Escalabilidad.
- Experiencia de desarrollo moderno.
- _Las contribuciones, reportes y sugerencias están abiertas._