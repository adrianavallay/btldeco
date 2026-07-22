# BTLDECO — Contexto del proyecto

> Este archivo lo lee Claude automáticamente al iniciar cada sesión.
> Sirve para retomar el trabajo sin tener que re-explicar todo.

## Qué es
Tienda online (e-commerce) en **PHP + MySQL** (sin framework). Pagos con MercadoPago.
- Sitio de **pruebas**: https://btldeco.com.ar
- Idioma de trabajo con la usuaria: **español**.

## Cómo se aplican los cambios (deploy)
- Todo va a **git**. El servidor hace **`git pull` por SSH**.
- Cambios de **código** → llegan solos con el `git pull`.
- Cambios de **base de datos** → se aplican abriendo **`/install.php`** (logueada como admin)
  y apretando "Aplicar cambios pendientes". Es idempotente.
- **Regla:** todo cambio de base de datos se hace como una **migración** en `migrations/`
  (ver `migrations/EXAMPLE.php.txt`). Nunca correr SQL suelto en el server.

## Estado de la base de código
- `install.php` + `migrations/` → sistema de migraciones idempotente (ya hecho).
- Importador de productos por CSV: `import_productos.php` (mapeo de columnas, upsert por slug).
- Auth actual: **un solo admin** con usuario/clave en `.env` (`ADMIN_USER`, `ADMIN_PASS_HASH`).
  No hay tabla de usuarios ni roles todavía.

## ⭐ Trabajo pendiente — LEER docs/PLAN.md
El plan completo y actualizado está en **`docs/PLAN.md`**. Es el documento vivo.
Si la usuaria menciona alguno de estos temas, el contexto está ahí:

- **Migración desde WordPress/WooCommerce** → traer productos, categorías e **imágenes**
  (todas) a esta tienda. Tiene acceso total a WP admin y a la base de WordPress.
  (Parte A del plan.)
- **Accesos/roles del admin** → crear sistema de usuarios con 3 roles:
  Administrador total, Gestor de productos, Gestor de pedidos/ventas. (Parte B del plan.)

## Convenciones
- Rama de desarrollo actual: `claude/proyecto-local-git-push-xcrmxb`.
- Commits y mensajes en español.
- Confirmar antes de fusionar a `main` (el server puede tomar de ahí).
