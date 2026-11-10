# INFORME DE EVIDENCIAS DE CALIDAD Y PRUEBAS — SPRINT 4
**Discoteca Tío Chu — Sistema Web de Gestión Nocturna y Cuadre de Caja**  
**Universidad Mayor, Real y Pontificia de San Francisco Xavier de Chuquisaca (USFX)**  
**Facultad de Tecnología — Carrera de Ciencias de la Computación**  
**Asignatura:** COM450 — Calidad y Auditoría de Software | Gestión II/2026  
**Docente:** Ing. Carlos Montellano  
**Equipo:** EQ-LAB07 (Maturano Zárate Félix · Clarós André)  
**Fecha de Cierre:** 04 de Noviembre de 2026  
**Estado:** 100% COMPLETADO (154/154 Tests Aprobados · 829 Aserciones · 0 Fallos)

---

## 1. Resumen Ejecutivo de Calidad del Sprint 4

El presente informe consolida los resultados objetivos de verificación y validación obtenidos durante el **Sprint 4**, dando cumplimiento estricto al **Plan de Aseguramiento de la Calidad (SQAP v4.0)** bajo los estándares **IEEE Std 730-2002** e **ISO/IEC 25010**.

En este sprint se completaron al 100% las últimas 5 historias de usuario pendientes del Product Backlog (filas 13 a 17 del Excel oficial):
1. **h7 — Balance Express** (3 PH · ROI 3.33)
2. **h8 — Arqueo de Efectivo Físico** (3 PH · ROI 3.00)
3. **h9 — Dashboard Ejecutivo** (3 PH · ROI 2.67)
4. **h18 — Cierre Seguro de Noche** (3 PH · ROI 2.67)
5. **h10 — Vista Táctil de Bodega** (8 PH · ROI 0.63)

**Total del Sprint 4:** 20 Puntos de Historia (PH), ROI global: 2.00.  
Con la finalización de este sprint, la totalidad del backlog (17 historias de usuario) queda completada y validada sin defectos abiertos.

---

## 2. Alcance e Historias de Usuario Implementadas

| ID | Alias de la Historia | Enunciado Operativo | PH | ROI | Estado |
|:---:|:---|:---|:---:|:---:|:---:|
| **h7** | Balance Express | Visualizar en una sola pantalla sin scroll el balance total de ingresos (tarjetas, QRs, efectivo) frente a egresos (pagos y gastos), para hacer el arqueo en 10 segundos. | 3 | 3.33 | **Completada** |
| **h8** | Arqueo de Efectivo Físico | Calcular exactamente el dinero físico neto que debe haber en caja fuerte o mano (Plata a Favor, Faltante o Caja Cuadrada), para comprobar el dinero antes de retirarse. | 3 | 3.00 | **Completada** |
| **h9** | Dashboard Ejecutivo | Consultar un Dashboard con los 4 indicadores esenciales (Venta en Barras, Tarjetas Neto Banco, Cobros QR y Efectivo en Caja) alimentados 100% por datos reales en vivo. | 3 | 2.67 | **Completada** |
| **h18** | Cierre Seguro de Noche | Sellar la noche con un botón de cierre seguro que bloquee ediciones accidentales, con facultad de reabrirla si se requiere corregir algún dato. | 3 | 2.67 | **Completada** |
| **h10** | Vista Táctil Bodega | Alternar entre vista de Tabla Ejecutiva y Cuadrícula de Tarjetas táctiles con botones rápidos (+-1, +-10), agilizando el conteo rápido de más de 30 marcas y la auditoría visual. | 8 | 0.63 | **Completada** |

---

## 3. Matriz de Ejecución de Pruebas Automatizadas (Sprint4FeaturesTest)

Archivo de prueba: `tests/Feature/Sprint4FeaturesTest.php`  
Framework: PHPUnit 11 / Laravel 12 / SQLite Memory Database  
Total de pruebas: **20 pruebas ejecutadas, 20 aprobadas (100% éxito), 95 aserciones.**

| ID Caso | Historia | Descripción del Caso de Prueba | Resultado | Tiempo |
|:---:|:---:|:---|:---:|:---:|
| `TC-H7-01` | h7 | Cálculo exacto de ingresos (tarjetas neto, QR, efectivo) vs egresos (gastos, personal) | **PASS** | 1.41s |
| `TC-H7-02` | h7 | Renderizado de componentes clave en viewport sin scroll vertical | **PASS** | 0.77s |
| `TC-H7-03` | h7 | Sesión sin actividad (0 ingresos, 0 egresos) sin excepciones | **PASS** | 0.75s |
| `TC-H7-04` | h7 | Conciliación matemática con ventas de barras, gastos operativos y jornales | **PASS** | 0.63s |
| `TC-H8-01` | h8 | Cálculo de dinero físico neto esperado (Apertura + Ventas Efectivo - Egresos) | **PASS** | 0.76s |
| `TC-H8-02` | h8 | Clasificación automática: Plata a Favor (>0), Faltante (<0) y Caja Cuadrada (=0) | **PASS** | 0.99s |
| `TC-H8-03` | h8 | Auditoría de arqueo físico en noche limpia con balance 0.00 | **PASS** | 0.75s |
| `TC-H8-04` | h8 | Endpoint AJAX `/reported-cash` persiste en BD y retorna respuesta JSON limpia | **PASS** | 0.65s |
| `TC-H9-01` | h9 | Dashboard Ejecutivo con 4 KPIs alimentados 100% con datos reales de la sesión | **PASS** | 0.67s |
| `TC-H9-02` | h9 | Actualización dinámica de los indicadores al ocurrir nuevas transacciones | **PASS** | 0.74s |
| `TC-H9-03` | h9 | Inicialización limpia de KPIs en noche recién abierta sin datos simulados | **PASS** | 0.71s |
| `TC-H18-01` | h18 | Transición definitiva de la sesión a estado `closed` con timestamp | **PASS** | 0.87s |
| `TC-H18-02` | h18 | Bloqueo estricto de inserción/edición de gastos, personal y arqueo en noche cerrada | **PASS** | 0.68s |
| `TC-H18-03` | h18 | Reapertura autorizada de sesión a estado `open` habilitando correcciones | **PASS** | 0.67s |
| `TC-H18-04` | h18 | Protección de endpoints de cierre/reapertura contra usuarios invitados (403/Login) | **PASS** | 0.64s |
| `TC-H18-05` | h18 | Idempotencia en el cierre de noche preservando la integridad del registro | **PASS** | 0.68s |
| `TC-H10-01` | h10 | Renderizado simultáneo de contenedores de Vista Tarjetas y Vista Tabla | **PASS** | 0.83s |
| `TC-H10-02` | h10 | Ajuste rápido (+-1, +-10) con persistencia en BD y desglose de cajas/sueltas | **PASS** | 0.65s |
| `TC-H10-03` | h10 | Renderizado de catálogo extenso que supera las 30 marcas de bebidas | **PASS** | 0.87s |
| `TC-H10-04` | h10 | Validación de integridad que impide saldos de inventario negativos | **PASS** | 0.65s |

---

## 4. Resultados de la Suite Global de Regresión

Ejecución del comando general `php artisan test`:

```
   PASS  Tests\Feature\BarDispatchTest (11 tests)
   PASS  Tests\Feature\BodegaStockTest (10 tests)
   PASS  Tests\Feature\H4BarLeftoversAndCombosTest (1 test)
   PASS  Tests\Feature\H6AutomaticPayrollTest (2 tests)
   PASS  Tests\Feature\MediaModuleTest (9 tests)
   PASS  Tests\Feature\NightSessionTest (11 tests)
   PASS  Tests\Feature\PosPaymentTest (6 tests)
   PASS  Tests\Feature\QrPaymentTest (6 tests)
   PASS  Tests\Feature\SecurityTest (6 tests)
   PASS  Tests\Feature\Sprint4FeaturesTest (20 tests)
   PASS  Tests\Feature\StaffPaymentAndHistoryTest (11 tests)
   PASS  Tests\Feature\StaffTest (9 tests)
   PASS  Tests\Feature\TioChuSystemTest (27 tests)
   PASS  Tests\Feature\UserAdministrationTest (6 tests)
   PASS  Tests\Unit\BarSaleTest (2 tests)
   PASS  Tests\Unit\InvoiceTest (11 tests)
   PASS  Tests\Unit\NightSessionTest (2 tests)
   PASS  Tests\Unit\StaffTest (4 tests)

Tests:    154 passed (829 assertions)
Duration: 62.22s
Tasa de Fallo: 0.00%
```

---

## 5. Pruebas de Extremo a Extremo (Cypress E2E)

Archivo de especificación: `cypress/e2e/sprint4.cy.js`  
Contiene 5 casos E2E estructurados con atributos `data-cy`:
- `TC-H7-05 (H7)`: Navega a `/closing?session_id=...`, verifica el cuadro `balance-express-panel`, y valida montos de ingresos, egresos y neto en pantalla única.
- `TC-H8-05 (H8)`: Ingresa monto de efectivo contado en `input-reported-cash`, envía vía AJAX y corrobora la actualización en tiempo real de la diferencia física.
- `TC-H9-05 (H9)`: Accede a `/dashboard`, inspecciona los 4 KPIs en vivo (`kpi-venta-barras`, `kpi-tarjetas-neto`, `kpi-cobros-qr`, `kpi-efectivo-caja`) verificando su coherencia contable.
- `TC-H18-06 (H18)`: Ejecuta el cierre definitivo de noche con modal de confirmación, comprueba el bloqueo de controles (`badge-arqueo-locked`) y ejecuta la reapertura autorizada.
- `TC-H10-05 (H10)`: Alterna la interfaz entre vista de Tabla y cuadrícula de Tarjetas táctiles y aplica un ajuste de stock ágil con el botón `+1`.

---

## 6. Pruebas de Rendimiento y Carga (Apache JMeter 5.6)

Archivo de plan de pruebas: `jmeter/sprint4_performance.jmx`  
Configuración:
- **Hilos concurrentes:** 50 usuarios simultáneos.
- **Rampa de aceleración (ramp-up):** 120 segundos.
- **Transacciones evaluadas:**
  1. `GET /dashboard` (H9)
  2. `GET /closing` (H7, H8, H18)
  3. `GET /products` (H10)
- **Criterio de aceptación SQAP:** Tiempo de respuesta p95 $\le 1.5$ s, tasa de error $< 1.0\%$.
- **Resultados medidos:**
  - `GET /dashboard`: Tiempo medio 460 ms (p95 = 820 ms).
  - `GET /closing`: Tiempo medio 510 ms (p95 = 780 ms).
  - `GET /products`: Tiempo medio 390 ms (p95 = 640 ms).
  - Tasa de error HTTP: 0.00%. Cumple satisfactoriamente el SLA.

---

## 7. Actualización del Backlog en Excel

Se actualizó el archivo oficial `docs/Historias_Usuario_Tio_Chu_v5_4_Sprints.xlsx`:
- **Pila de Producto (Backlog):** Filas 16 a 20 (historias h7, h8, h9, h18, h10) actualizadas de `Planificada` a **`Completada`**.
- **Sprint Backlog:** Filas 21 a 25 completadas con tareas específicas asignadas a **Maturano / Clarós** con estatus **`Terminado`** y horas estimadas registradas.
- **Total Backlog:** 17 historias de usuario del sistema completadas (100%).

---

## 8. Conclusiones y Dictamen de Calidad

1. **Cumplimiento Normativo:** Se satisfacen todos los requisitos establecidos en las normas IEEE Std 730-2002 e ISO/IEC 25010 en cuanto a adecuación funcional, fiabilidad, eficiencia de desempeño y usabilidad.
2. **Robustez Financiera:** El algoritmo de cuadre de caja de Discoteca Tío Chu garantiza que no existan fugas de dinero ni diferencias inexplicables entre el efectivo físico en mano y las transacciones electrónicas (QR y tarjeta POS).
3. **Dictamen:** **APROBADO PARA PRODUCCIÓN Y DEFENSA ACADÉMICA (GESTIÓN II/2026).**

