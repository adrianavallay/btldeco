# Plan de trabajo — BTLDECO

> Documento vivo. Se va actualizando a medida que avanzamos.
> Última actualización: 2026-07-22

---

## Estado del proyecto

- Sitio de **pruebas**: https://btldeco.com.ar
- Repo: `dypconsultora/btldeco`
- Deploy: se sube todo a **git** → el servidor hace `git pull` por **SSH**.
- Los cambios de **código** llegan solos con el `git pull`.
- Los cambios de **base de datos** se aplican abriendo `/install.php` (ver abajo).

### Ya hecho ✅
- [x] Instalador de base de datos con sistema de migraciones (`install.php` + `migrations/`).
- [x] Arreglo de los gráficos del dashboard (no se renderizaban).
- [x] PR #1 abierto hacia `main` con ambos cambios.

---

## Cómo aplicamos los cambios (recordatorio)

1. Yo hago los cambios y los **subo a git**.
2. El servidor hace **`git pull`** (por SSH).
3. Si el cambio toca la **base de datos**: entrás a `https://btldeco.com.ar/install.php`
   logueada como admin y apretás **"Aplicar cambios pendientes"**. Es seguro repetirlo.
4. Si es solo código: con el `git pull` alcanza (recargá con **Cmd/Ctrl + Shift + R**).

---

## Decisiones tomadas

- La web actual usa **WooCommerce**.
- Se tiene **acceso total**: WP admin + base de datos de WordPress.
- **Sí** se migran las **imágenes** (todas: principal + galería).
- Roles del admin a crear: **Administrador total**, **Gestor de productos**, **Gestor de pedidos/ventas**.

---

## PARTE A — Migrar datos de WooCommerce → BTLDECO

> El importador de productos **ya existe** (`import_productos.php`): mapea columnas,
> hace upsert por slug y resuelve la categoría por nombre.

- [ ] **A0 · Exportar de WooCommerce** — `WooCommerce → Productos → Exportar → CSV`
      (el CSV trae textos, precios, stock, categorías y las **URLs de las imágenes**).
- [ ] **A1 · Categorías** — mejorar el importador para que **cree solas** las categorías
      que no existan.
- [ ] **A2 · Productos** — importar el CSV con la herramienta existente, mapeando las
      columnas de WooCommerce a las de acá (upsert por slug, no duplica).
- [ ] **A3 · Imágenes** — script nuevo que **descarga cada foto desde la URL de WordPress**
      y la sube a `/uploads/productos/`, asignando imagen principal + galería.

### Campos que se trasladan
| WooCommerce | BTLDECO |
|---|---|
| Name | nombre |
| Description | descripcion |
| Short description | descripcion_corta |
| Regular price | precio |
| Sale price | precio_oferta |
| Stock | stock |
| Categories | categoria (por nombre) |
| Images (URLs) | imagen principal + galería |

### Detalles a resolver en el camino ⚠️
- **Jerarquía de categorías:** WooCommerce usa *Padre > Hijo*; acá son planas.
  Decidir si usar la categoría "hija" o aplanar.
- **SEO (Yoast/RankMath):** no viene en el export estándar. Si se quiere, se saca
  aparte desde la base de WordPress.

---

## PARTE B — Sistema de accesos del admin (3 roles)

> Hoy hay **un solo** admin con usuario/clave fijos en `.env`. No hay tabla de
> usuarios ni roles. Se construye desde cero.

### Matriz de permisos
| Sección | Admin total | Gestor productos | Gestor pedidos/ventas |
|---|:---:|:---:|:---:|
| Productos · Categorías · Slider | ✅ | ✅ | — |
| Pedidos · Clientes · Cupones | ✅ | — | ✅ |
| Reportes | ✅ | lo suyo | lo suyo |
| Configuración · Usuarios · Instalador | ✅ | — | — |

### Tareas
- [ ] **B1** — Migración: tabla `usuarios_admin` (nombre, email, contraseña, rol, activo).
- [ ] **B2** — Login contra esa tabla (se mantiene el admin del `.env` como llave maestra).
- [ ] **B3** — Helper `require_rol()` para bloquear páginas según el rol.
- [ ] **B4** — Ocultar/mostrar secciones del menú según el rol.
- [ ] **B5** — Pantalla para crear/editar/desactivar usuarios (solo Admin total).

---

## Orden sugerido

1. **Parte A** (migrar datos) — hace que la tienda tenga contenido real; el 80% ya existe.
2. **Parte B** (accesos) — infraestructura, se hace después con calma.

Las dos partes son independientes; se puede cambiar el orden.
