# Manual de Usuario — Tío Chu App

## 1. Acceso

1. Abre la URL de la instalación y espera el formulario de inicio de sesión.
2. Ingresa correo y contraseña habilitados. El sistema limita intentos repetidos; si se alcanza el límite, espera antes de reintentar.
3. Al autenticarte, se abre el dashboard. Usa **Cerrar sesión** al terminar; la sesión también puede expirar por inactividad.
4. Las cuentas administradoras tienen acceso a administración de usuarios, reapertura y funciones administrativas. El túnel `/server` existe solo en entorno local.

## 2. Abrir o seleccionar una noche

1. Entra a **Sesiones** (`/sessions`) y revisa la fecha/estado antes de operar.
2. Usa la opción de crear sesión para la fecha del turno. La fecha es única; no crees otra noche para una fecha ya registrada.
3. En el formulario de apertura, completa la fecha, los datos solicitados y el efectivo inicial si corresponde.
4. Abre la sesión creada o selecciónala en el dashboard. Verifica siempre la fecha y estado de la sesión activa antes de registrar ventas.
5. Para una sesión cerrada, solicita a un administrador la reapertura; las acciones de estado/eliminación están restringidas.

## 3. Preparar inventario

### Inventario de bodega

1. Entra a **Productos** (`/products`) para consultar el catálogo, precio, categoría y estado.
2. Usa la acción de stock rápido o el formulario del producto para registrar movimientos de existencias.
3. Comprueba el stock de almacén después de guardar. Las ventas directas de tienda descuentan stock y dejan registro de movimiento.

### Inventario de barra

1. Abre **Inventario de barra** (`/bar-inventory`).
2. Selecciona la noche y revisa el filtro/buscador para localizar bebidas; la lista puede estar paginada.
3. Ingresa cantidades iniciales por producto/barra y guarda los cambios en lote.
4. Al cierre, registra saldo restante con atención al producto y la unidad. Si corresponde, sincroniza el inventario desde la noche anterior y verifica las cantidades antes de confirmar.

## 4. Registrar ventas y pagos

### Ventas de barra

1. Abre **Ventas** (`/sales`) y confirma la sesión activa.
2. Busca el producto con el filtro si no aparece en la primera página.
3. Ingresa cantidades vendidas y, cuando aplique, los mixers seleccionados. Revisa subtotal y totales antes de guardar el lote.
4. Para una venta directa de tienda, usa el formulario de tienda e ingresa producto, cantidad y desglose de pago solicitado.

### Pagos QR y facturas

1. En **Pagos QR** (`/qrs`), registra operador, aplicación (YASTA o YAPE), importe y punto de venta.
2. Comprueba que el importe y el punto de venta correspondan con el comprobante externo. La aplicación registra el pago; no realiza la transferencia bancaria.
3. En **Facturas** (`/invoices`), ingresa correlativo, método de pago, importe y datos de comisión que solicite el formulario.
4. Verifica que los registros aparezcan asociados a la sesión correcta.

### Pagos de cerveza

1. Desde el dashboard, usa el bloque de pagos de cerveza para registrar el importe, método (efectivo/QR) y proveedor QR cuando corresponda.
2. Confirma la operación y verifica que el nuevo registro aparezca en el resumen de la sesión.

## 5. Personal, asistencia y pagos

1. En **Personal** (`/staff`), consulta o actualiza el personal activo y sus datos operativos.
2. En **Pagos de personal** (`/staff-payments`), selecciona la sesión, marca asistencia, revisa el monto y guarda.
3. Usa el marcado por lote únicamente después de verificar todas las filas seleccionadas.
4. En **Historial de pagos** (`/payment-history`), consulta pagos/deudas y usa el pago individual para liquidar una asistencia pendiente.
5. Revisa que el estado pagado y el monto queden actualizados antes de cerrar caja.

## 6. Arqueo y cierre de caja

1. Abre **Cierre** (`/closing`) para la sesión correcta.
2. Registra gastos con categoría, descripción e importe. Corrige o elimina duplicados antes del cierre.
3. Revisa asistencia/pagos, tarjetas, QR, efectivo calculado y total de ventas.
4. Cuenta el efectivo físico e ingresa el monto reportado en el campo de arqueo. Compara la diferencia y resuelve discrepancias antes de cerrar.
5. Cuando los datos estén revisados, ejecuta **Cerrar noche** y confirma la acción.
6. Una noche cerrada no debe modificarse mediante acciones bloqueadas. Si se requiere una corrección, pide a un administrador que la reabra; registra el motivo según el procedimiento interno y vuelve a cerrar tras verificar.

## 7. Dashboard y consolidado

1. En **Dashboard** (`/dashboard`), verifica fecha de sesión y los indicadores de ventas, tarjetas, QR, efectivo y egresos.
2. Usa el consolidado de sesiones para comparar noches o periodos seleccionados.
3. Si un total parece incorrecto, vuelve al detalle de ventas/pagos/gastos asociado; no dupliques un registro para corregir otro.

## 8. Galería multimedia

1. Un usuario autenticado puede abrir **Multimedia** (`/media`) y cargar imágenes permitidas por el formulario.
2. Revisa nombre/categoría y confirma la carga. Usa reemplazar/eliminar solo cuando el recurso corresponda.
3. Para problemas de visualización, actualiza la página y comprueba que el archivo siga listado; evita subir archivos ajenos al uso operativo.

## 9. Incidencias frecuentes

| Síntoma | Acción recomendada |
|---|---|
| Redirige al login o muestra sesión expirada | Vuelve a iniciar sesión y repite la operación solo después de comprobar si el registro ya se guardó. |
| Respuesta 403 | Verifica que la sesión esté abierta y que tu cuenta tenga el rol requerido; contacta a un administrador si la acción es administrativa. |
| Producto/personal no visible | Usa el buscador, filtros o paginación de la vista. |
| Total de caja no coincide | Revisa la sesión activa, ventas, pagos QR/tarjeta, pagos de personal, gastos y efectivo reportado; no edites una noche cerrada sin autorización. |
| Error al cargar imagen | Confirma formato/tamaño aceptado por el formulario y vuelve a intentar con un archivo válido. |
| Aplicación inaccesible después de despliegue | Comunica al responsable técnico; no repitas comandos de migración/seed desde el navegador. |

Los roles `bartender`, `cajero` y `bodeguero` no tienen permisos diferenciados en esta versión salvo las rutas explícitamente reservadas a administradores. Sigue el procedimiento operativo local para asignar responsabilidades.
