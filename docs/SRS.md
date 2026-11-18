# Especificación de Requisitos de Software (SRS)

**Sistema:** Tío Chu App — Gestión Operativa Nocturna  
**Versión:** 1.0  
**Base de elaboración:** rutas, controladores, modelos, migraciones y pruebas del repositorio.

## 1. Introducción

### 1.1 Propósito

Este documento especifica las funciones y restricciones verificables de Tío Chu App para usuarios operativos y administradores del bar y licorería Tío Chu. Su estructura toma como referencia IEEE 830 e ISO/IEC/IEEE 29148; las rutas Laravel citadas constituyen la trazabilidad de la implementación.

### 1.2 Alcance

La aplicación registra sesiones nocturnas, productos e inventario, ventas, pagos, asistencia, gastos y cierres. Presenta resúmenes para la operación y permite administrar usuarios y activos multimedia. No reemplaza un sistema contable fiscal ni procesa directamente pagos bancarios: captura y consolida los datos ingresados por el personal.

### 1.3 Definiciones

- **Sesión / noche:** operación identificada por fecha, con estado `open` o `closed`.
- **Operador:** usuario autenticado que utiliza las funciones operativas. La base permite los roles `bartender`, `cajero` y `bodeguero`; actualmente estos roles comparten el acceso autenticado general y no tienen permisos diferenciados entre sí.
- **Administrador:** usuario con `role=admin`; además de las funciones operativas, accede a las rutas explícitamente protegidas para administración.
- **QR:** pago registrado indicando aplicación (YASTA o YAPE), importe y punto de venta.

### 1.4 Referencias

- [Rutas web](../routes/web.php)
- [Modelos Eloquent](../app/Models)
- [Migraciones](../database/migrations)
- [Pruebas Feature](../tests/Feature)
- [Pruebas Cypress](../cypress/e2e)
- [Manual de Usuario](manual/MANUAL_USUARIO.md)

## 2. Descripción general

### 2.1 Perspectiva del producto

Aplicación web Laravel con interfaz Blade, sesiones de usuario y persistencia relacional. Los módulos operativos se agrupan alrededor de `NightSession`; las ventas, pagos, asistencias y gastos se asocian a una noche para consolidar el cierre.

### 2.2 Clases de usuario

| Clase | Identificación implementada | Capacidades |
|---|---|---|
| Administrador | `users.role = admin` | Funciones operativas autenticadas, administración de usuarios, cambio/eliminación de sesiones, reapertura de cierre y túnel de servidor solo en entorno `local`. |
| Operador | `bartender`, `cajero` o `bodeguero` | Funciones bajo autenticación que no tienen middleware `admin`. No hay autorización granular por cada rol en la versión documentada. |

### 2.3 Entorno operativo y restricciones

- PHP/Laravel ejecutado en servidor web; el contenedor oficial usa PHP 8.4 y Apache.
- SQLite es la conexión predeterminada de desarrollo/pruebas. Laravel también ofrece configuración para otros motores relacionales.
- El navegador debe aceptar cookies de sesión y tokens CSRF para las solicitudes mutadoras.
- Los importes se persisten como decimales; el sistema depende de que el operador seleccione la sesión correcta antes de registrar movimientos.
- El almacenamiento local efímero de Render no debe considerarse almacenamiento persistente.

## 3. Requisitos funcionales

| ID | Requisito | Trazabilidad de implementación |
|---|---|---|
| FR-01 | El sistema permitirá iniciar y cerrar sesión; limitará intentos de login a cinco por minuto. | `GET/POST /login`, `POST /logout`; `AuthController`, middleware `throttle:5,1`. |
| FR-02 | Permitirá crear y consultar noches por fecha y mantener su estado abierto/cerrado. El cambio de estado y eliminación requieren administrador; las sesiones cerradas no deben admitir mutaciones protegidas por el controlador. | `GET/POST /sessions`, `PATCH /sessions/{session}/status`, `DELETE /sessions/{session}`; `NightSessionController`. |
| FR-03 | Permitirá consultar y administrar el catálogo, precios, categorías e inventario base de productos. | Recurso `/products` y `PATCH /products/{product}/quick-stock`; `ProductController`. |
| FR-04 | Permitirá registrar y consultar stock inicial/saldo de productos por barra y sincronizar inventario desde una noche anterior. | `GET /bar-inventory`, `PUT /bar-inventory/bulk-update`, `POST /bar-inventory/sync-previous`; `BarInventoryController`. |
| FR-05 | Permitirá registrar ventas de barra, mixers especiales y ventas directas de tienda asociadas a la noche. | `GET /sales`, `PUT /sales/bulk-update`, `POST /sales/store-sales`, `DELETE /sales/store-sales/{storeSale}`, `POST /sales/tienda-extras`, `POST /sales/special-mixers`; `BarSaleController`, `StoreSaleController`, `BarSaleCalculationService`. |
| FR-06 | Permitirá capturar pagos QR con operador, aplicación y punto de venta, y consultar/eliminar registros. | `GET/POST /qrs`, `DELETE /qrs/{qr}`; `QrPaymentController`. |
| FR-07 | Permitirá registrar facturas con importe, correlativo, método de pago y comisión; permitirá listar y eliminar. | `GET/POST /invoices`, `DELETE /invoices/{invoice}`; `InvoiceController`. |
| FR-08 | Permitirá mantener personal y asistencia por noche, registrar y actualizar pagos, marcar lotes como pagados y consultar el historial/deudas. | `/staff`, `/staff-payments`, `/payment-history`; `StaffController`, `StaffPaymentController`, `PaymentHistoryController`. |
| FR-09 | Permitirá registrar pagos de cerveza en efectivo o QR y reflejarlos en el dashboard. | `POST/DELETE /beer-payments`; `DashboardController`. |
| FR-10 | Permitirá registrar/eliminar gastos, administrar asistencia desde cierre, calcular arqueo, registrar efectivo reportado y cerrar una noche; la reapertura requiere administrador. | `/closing`, `POST /closing/{session}/close`, `POST /closing/{session}/reopen`; `CashClosingController`, `NightSessionService`. |
| FR-11 | Permitirá consultar indicadores ejecutivos de la noche activa/seleccionada y un resumen consolidado de noches. | `GET /dashboard`, `GET|POST /sessions/consolidated-summary`; `DashboardController`, `NightSessionController`, `NightSessionService`. |
| FR-12 | Permitirá consultar, cargar, reemplazar, clasificar y eliminar activos multimedia, así como actualizar avatar. | `/media`, `/profile/avatar`; `MediaController`. |
| FR-13 | Permitirá a administradores administrar cuentas de usuario y roles. Las rutas `/server/*` estarán restringidas a administrador y al entorno local. | Recurso `/users` con `admin`; grupo `/server` con `admin:local`; `UserController`, `ServerTunnelController`, `EnsureAdmin`. |

## 4. Requisitos no funcionales

| ID | Requisito | Criterio / estado |
|---|---|---|
| NFR-01 Rendimiento | Bajo el plan JMeter documentado, p95 será `<= 1,500 ms`, throughput `>= 30 req/s` y error `< 1%`. | Artefacto Sprint 4: p95 727.95 ms, 120.90 req/s, 0.04% de muestras fallidas. |
| NFR-02 Tamaño inicial | Las vistas paginadas/livianas de productos, personal, ventas e inventario mantendrán el HTML inicial bajo 250 KB y conservarán selectores `data-cy`. | Objetivo probado por `InitialHtmlPayloadTest`; el umbral aplica a las vistas incluidas en esa prueba. |
| NFR-03 Seguridad de sesión | Las rutas operativas requieren autenticación; las solicitudes mutadoras están protegidas por CSRF; el login aplica limitación de tasa. | Rutas y middleware Laravel. |
| NFR-04 Seguridad HTTP | El sistema enviará CSP y cabeceras de seguridad; HSTS se añade en respuestas HTTPS. En producción, URLs se fuerzan a HTTPS. | `SecurityHeadersMiddleware`, `AppServiceProvider`; el CSP permite Vite/HMR local solo en entorno `local`. |
| NFR-05 Autorización | Las funciones administrativas y el túnel se limitarán según rol y entorno. | `EnsureAdmin`; middleware `admin` y `admin:local`. |
| NFR-06 Persistencia | La información de operación debe residir en almacenamiento persistente, con copias de seguridad administradas por el operador de despliegue. | SQLite local es predeterminado; para Render se requiere volumen persistente o base administrada. La política de backup no está automatizada por la aplicación. |
| NFR-07 Disponibilidad | El servicio debe estar disponible durante los turnos de operación y recuperarse ante reinicios del contenedor. | No existe SLO numérico ni prueba de disponibilidad continua en el repositorio; el objetivo porcentual requiere acuerdo operacional. |
| NFR-08 Mantenibilidad | El código PHP debe cumplir Pint y analizarse con PHPStan nivel 5 sobre `app/`. | Comandos obligatorios documentados en README y CI. |

## 5. Interfaces y datos

La interfaz principal es web responsiva. Las entidades relacionales incluyen usuarios, productos, personal, sesiones nocturnas, ventas, facturas, pagos QR, ventas directas de tienda, gastos, pagos de cerveza, asistencias, cierres de caja, movimientos de stock, opciones de mixers y activos multimedia. Las relaciones y claves principales se detallan en [SAD](SAD.md).

## 6. Criterios de aceptación

1. Los requisitos FR deben poder ejecutarse mediante las rutas indicadas con una cuenta autenticada apropiada.
2. Toda ruta protegida para administrador debe responder con rechazo de autorización ante un usuario no administrador.
3. Las operaciones de cierre deben reflejar ventas, pagos, nómina y gastos de la sesión seleccionada.
4. El conjunto automatizado reportado al cierre debe aprobarse íntegramente; las métricas y límites se registran en el [SQAP Maestro](SQAP-Maestro-General-v4.0.md).
5. Las pruebas presenciales de usabilidad no se consideran completadas por la existencia de pruebas automatizadas.
