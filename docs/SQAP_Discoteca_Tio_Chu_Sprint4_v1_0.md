# Plan de Aseguramiento de Calidad de Software (SQAP v4.0)
## Sistema de Gestión Nocturna “Discoteca Tío Chu”
### Sprint 4 — Balance final, arqueo y dashboard

---

**Código del Documento:** SQAP-EQ-LAB07-v4.0  
**Versión:** 4.0 — Consolidada para revisión, ejecución y aprobación académica  
**Institución:** Universidad San Francisco Xavier de Chuquisaca (USFX)  
**Facultad:** Facultad de Ciencia y Tecnología  
**Carrera:** Ciencias de la Computación  
**Asignatura:** COM450 — Calidad de Software  
**Docente:** Ing. Carlos Montellano  
**Gestión:** II/2026  
**Lugar y Fecha:** Sucre, Bolivia — Noviembre de 2026  
**Equipo Responsable (EQ-LAB07):**
- Maturano Zárate Félix (Líder de Proyecto / Auditor SQA)
- Clarós André (Ingeniero de Desarrollo y Pruebas)

---

## 1. Propósito

Este **Plan de Aseguramiento de Calidad de Software (SQAP v4.0)** establece las actividades, responsabilidades, estándares, métricas, revisiones, pruebas, controles y evidencias aplicables a la entrega del **Sprint 4** del Sistema de Gestión Nocturna “Discoteca Tío Chu”. Su propósito es garantizar y demostrar de forma reproducible que las cinco historias de usuario finales de la Pila de Producto cumplen estrictamente las especificaciones del SRS, operan de forma segura y consistente bajo concurrencia, y mantienen una trazabilidad completa libre de defectos críticos.

Este documento adopta la estructura formal de 16 secciones y anexos definida por **IEEE Std 730-2002**, articulada con el modelo de calidad de producto **ISO/IEC 25010:2011**, el estándar de revisiones e inspecciones **IEEE Std 1028-2008** y las directrices metodológicas de la asignatura COM450 de la USFX.

### 1.1 Alcance funcional del Sprint 4

El Sprint 4 abarca exactamente las 5 historias de usuario pendientes (filas 13 a 17 del Product Backlog v5), totalizando **20 Puntos de Historia (PH)** y un **ROI ponderado de 2.00**:

| ID | Historia de Usuario | Alias | PH | PV | ROI | Prioridad | Depende de | Criterio Verificable / Objetivo de Aceptación |
|---|---|---|---|---|---|---|---|---|
| **h7** | Balance Express | `balance-express` | 3 | 10 | 3.33 | ALTA | h13, h14, h4, h5, h16, h17 | Visualizar en una sola pantalla sin scroll el balance total de ingresos (tarjetas, QRs, efectivo) frente a egresos (pagos y gastos) en menos de 10 segundos. |
| **h8** | Arqueo Efectivo Físico | `arqueo-fisico` | 3 | 9 | 3.00 | ALTA | h5, h16, h17 | Calcular exactamente el dinero físico neto que debe haber en caja fuerte o mano (Plata a Favor, Faltante o Caja Cuadrada) antes del retiro del administrador. |
| **h9** | Dashboard Ejecutivo | `dashboard-ejecutivo` | 3 | 8 | 2.67 | ALTA | h4, h13, h14, h8 | Consultar un Dashboard con los 4 indicadores esenciales (Venta en Barras, Tarjetas Neto Banco, Cobros QR y Efectivo en Caja) alimentados 100% por transacciones reales. |
| **h18** | Cierre Seguro de Noche | `cierre-noche` | 3 | 8 | 2.67 | ALTA | h2, h7, h8 | Sellar la noche con un botón de cierre seguro que bloquee ediciones accidentales en ventas, stock y pagos, con facultad de reapertura autorizada. |
| **h10** | Vista Táctil Bodega | `vista-tactil-bodega` | 8 | 5 | 0.63 | BAJA | h11 | Alternar entre Vista de Tabla Ejecutiva y Cuadrícula de Tarjetas táctiles con controles de paso (-10, -1, +1, +10) para más de 30 marcas de bebidas. |
| **Total** | **5 Historias** | — | **20** | **40** | **2.00** | — | — | **Cierre del 100% de la Pila de Producto institucional.** |

> [!NOTE]
> Las historias completadas en los Sprints 1, 2 y 3 constituyen la suite de regresión obligatoria. Ningún merge a la rama principal será permitido si algún caso de prueba de los sprints anteriores falla.

### 1.2 Clasificación de integridad y criticidad

El proyecto se ratifica en **Nivel de Integridad 2 (Moderado)** según IEEE Std 1012: una falla contable o de sellado de noche puede derivar en discrepancias en caja, arqueos erróneos o liquidaciones imprecisas, requiriendo verificación matemática rigurosa, integridad referencial y aserciones de consistencia en el DOM.

### 1.3 Objetivos de aseguramiento de calidad del Sprint 4

1. **Eficacia de Pruebas:** Ejecutar el 100% de los casos de prueba obligatorios definidos para h7, h8, h9, h18 y h10.
2. **Cobertura de Código:** Mantener una cobertura de líneas superior al 80% global y al 90% en lógica contable y controladores.
3. **Control de Defectos:** Cero defectos abiertos de severidad S1 (Crítico) y S2 (Mayor) en la auditoría de cierre.
4. **Validación E2E:** 100% de escenarios Cypress aprobados en modo headless (`cypress run`) con capturas de evidencia fotográfica.
5. **Rendimiento de Carga:** Percentil 95 (p95) $\le 1.5$ segundos bajo una carga de 50 usuarios concurrentes en Apache JMeter.
6. **Formato y Estilo:** Cumplimiento total de PSR-12 verificado automáticamente con Laravel Pint sin advertencias.

---

## 2. Documentos de referencia

- **SRS v1.0:** Especificación de Requisitos de Software — Sistema Discoteca Tío Chu.
- **SAD v1.0:** Documento de Arquitectura de Software — Patrones MVC, servicios y persistencia.
- **PB v5.0:** `Historias_Usuario_Tio_Chu_v5_4_Sprints.xlsx` — Pila de Producto consolidada en 4 Sprints.
- **SQAP Maestro v3.0:** Plan Maestro de Aseguramiento de Calidad para Sprints 1 a 4.
- **IEEE Std 730-2002:** Standard for Software Quality Assurance Plans.
- **ISO/IEC 25010:2011:** Systems and software engineering — Systems and software Quality Requirements and Evaluation (SQuaRE).
- **IEEE Std 1028-2008:** Standard for Software Reviews and Audits.
- **PSR-12:** Extended Coding Style Guide (PHP-FIG).
- **Cypress & Apache JMeter:** Herramientas de automatización funcional E2E y pruebas de rendimiento.

---

## 3. Gestión

### 3.1 Organización y responsabilidades
- **Félix Maturano Zárate:** Líder de Calidad / Auditor SQA. Responsable del cumplimiento del SQAP, diseño de escenarios de carga en JMeter, auditoría de métricas y aprobación formal de PRs.
- **André Clarós:** Ingeniero de Desarrollo y Automatización. Responsable de la implementación funcional de las 5 historias, elaboración de suites PHPUnit/Pest, automatización Cypress y corrección de defectos.

### 3.2 Regla de independencia de SQA
Ningún integrante puede aprobar su propio código ni certificar sus propios artefactos. Los desarrollos y pruebas automatizadas elaboradas por André deben someterse a la revisión técnica independiente de Félix antes de incorporarse a `main`.

### 3.3 Matriz RACI para el Sprint 4

| Actividad / Entregable | Félix (SQA) | André (Dev/Test) | Docente USFX | Don Ludo (Cliente) |
|---|:---:|:---:|:---:|:---:|
| Casos de prueba unitarios e integración (Pest/PHPUnit) | **A** | **R** | I | I |
| Automatización de pruebas E2E en Cypress | **A** | **R** | I | I |
| Pruebas de carga con 50 usuarios (JMeter) | **R** | **C** | I | I |
| Peer Review de código y Merge a `main` | **A / R** | **C** | I | I |
| Auditoría de Cierre de Sprint 4 e Informe de Evidencias | **R** | **C** | **A** | I |
| Validación de Criterios de Aceptación del Negocio | **C** | **C** | I | **A** |

*R = Responsable, A = Aprobador, C = Consultado, I = Informado.*

---

## 4. Documentación

Artefactos mínimos obligatorios conservados en el repositorio:
1. `docs/SQAP_Discoteca_Tio_Chu_Sprint4_v1_0.docx` y `.md` (Plan de Calidad).
2. `docs/Historias_Usuario_Tio_Chu_v5_4_Sprints.xlsx` actualizado con Sprint 4 completado.
3. `tests/Feature/Sprint4FeaturesTest.php` (Suites unitarias y de integración).
4. `cypress/e2e/sprint4.cy.js` y capturas en `cypress/screenshots/sprint4/`.
5. `jmeter/sprint4_performance.jmx` y resultados de carga.
6. `docs/EVIDENCIAS_SPRINT_4.docx` (Dossier de evidencias de calidad).

---

## 5. Estándares, prácticas, convenciones y métricas

### 5.1 Estándares de codificación
- Convención PSR-12 obligatoria mediante `./vendor/bin/pint`.
- Convención de commits semánticos (`feat:`, `fix:`, `test:`, `docs:`, `perf:`) regulada por Husky y Commitlint.
- Selectores de prueba desacoplados mediante atributos `data-cy="..."`.

### 5.2 Convenciones operativas
- Operaciones monetarias redondeadas a 2 decimales (`ROUND_HALF_UP`).
- Transacciones de base de datos (`DB::transaction`) para operaciones compuestas (cierre de noche, balance, recálculo de arqueo).
- Control de sesión: rechazo estricto de cualquier mutación sobre una noche en estado `closed` con código HTTP 403 o redirección controlada.

### 5.3 Métricas de calidad del producto (ISO/IEC 25010)

| ID | Característica | Métrica y Fórmula | Umbral de Aceptación |
|---|---|---|---|
| **M-01** | Adecuación funcional | $\text{Tasa Aprobación} = \frac{\text{Pruebas Aprobadas}}{\text{Pruebas Totales}} \times 100$ | **100 %** (0 pruebas fallidas) |
| **M-02** | Mantenibilidad | Cobertura de líneas en componentes críticos | $\ge 80\,\%$ global, $\ge 90\,\%$ lógica contable |
| **M-03** | Eficiencia de desempeño | Tiempo de respuesta percentil 95 (p95) en JMeter | **p95 $\le 1.5$ s** con 50 usuarios |
| **M-04** | Fiabilidad | Tasa de error HTTP en carga concurrente | **$< 1.0\,\%$** de errores |
| **M-05** | Usabilidad táctil | Dimensiones de controles touch en bodega (h10) | **$\ge 44 \times 44\text{ px}$** en touch targets |
| **M-06** | Seguridad | Protección de rutas ante noche cerrada | **100 %** de endpoints mutadores protegidos |
| **M-07** | Mantenibilidad | Duplicación de código nuevo (análisis estático) | **$< 3\,\%$** |

---

## 6. Revisiones de software

- **Revisión de Requisitos:** Verificación de las historias de usuario 13 a 17 contra las necesidades reales de Don Ludo.
- **Inspección Técnica Fagan Adaptada:** Revisión formal de Pull Requests previa a integración a `main` validando seguridad, transaccionalidad y pruebas.
- **Auditoría de Cierre de Sprint:** Comprobación integral de los umbrales de aceptación del SQAP antes de la entrega final.

---

## 7. Pruebas

### 7.1 Niveles de prueba
1. **Unitario:** Verificación de algoritmos de arqueo, cálculo de balance express y detección de diferencias de caja.
2. **Integración:** Peticiones HTTP a controladores Laravel (`DashboardController`, `CashClosingController`, `ProductController`) verificando persistencia y respuestas JSON/Blade.
3. **Sistema E2E:** Flujos completos ejecutados en Cypress headless simulando la navegación real de Don Ludo.
4. **Carga y Rendimiento:** Simulación de 50 usuarios en JMeter midiendo estabilidad, throughput y p95.

### 7.2 Casos de prueba detallados para Sprint 4

#### Historia h7 — Balance Express (3 PH · ROI 3.33)
- **TC-H7-01:** Consultar balance con ingresos (tarjetas neto, QRs, efectivo) y egresos (pagos de personal y gastos). Los totales coinciden con la suma contable del SRS.
- **TC-H7-02:** Verificar que el balance express cabe en una sola pantalla sin scroll vertical en resolución estándar ($\ge 1080\text{p}$).
- **TC-H7-03:** Consultar balance de noche sin movimientos (ingresos = 0, egresos = 0); no genera errores ni excepciones.
- **TC-H7-04:** Contrastar balance con liquidación de barras (h5), pagos (h16) y deudas saldadas (h17).
- **TC-H7-05 (Cypress E2E):** Ingresar a Cierre de Caja y validar cuadro Balance Express; aprueba en `cypress run`.

#### Historia h8 — Arqueo de Efectivo Físico (3 PH · ROI 3.00)
- **TC-H8-01:** Calcular el dinero físico neto esperado a partir de ventas en efectivo menos egresos pagados en mano.
- **TC-H8-02:** Comparar efectivo reportado contra esperado y verificar clasificación automática: `Plata a Favor` ($> 0$), `Faltante` ($< 0$) o `Caja Cuadrada` ($= 0$).
- **TC-H8-03:** Calcular arqueo en noche sin movimientos; resultado en 0.00 sin fallos.
- **TC-H8-04:** Calcular arqueo tras amortizar jornales atrasados (h17); se descuenta correctamente del efectivo disponible.
- **TC-H8-05 (Cypress E2E):** Ingresar monto contado en formulario de arqueo y verificar cálculo dinámico de diferencia; aprueba en `cypress run`.

#### Historia h9 — Dashboard Ejecutivo (3 PH · ROI 2.67)
- **TC-H9-01:** Consultar los 4 KPIs (Venta en Barras, Tarjetas Neto Banco, Cobros QR y Efectivo en Caja) alimentados 100% con datos reales de la sesión.
- **TC-H9-02:** Registrar una nueva transacción (cobro QR o venta) y verificar que los indicadores se actualizan dinámicamente.
- **TC-H9-03:** Consultar Dashboard de una noche recién abierta; indicadores reflejan saldos iniciales de apertura sin números simulados.
- **TC-H9-04:** Medir tiempo de respuesta del Dashboard en JMeter; p95 $\le 1.5$ s.
- **TC-H9-05 (Cypress E2E):** Cargar Dashboard y corroborar los 4 KPIs contra la base de datos; aprueba en `cypress run`.

#### Historia h18 — Cierre Seguro de Noche (3 PH · ROI 2.67)
- **TC-H18-01:** Ejecutar acción "Cerrar Noche Definitiva"; la noche pasa a estado `closed` con confirmación.
- **TC-H18-02:** Intentar editar ventas, inventario, pagos o gastos en noche sellada; el sistema bloquea la acción con mensaje controlado.
- **TC-H18-03:** Ejecutar acción "Reabrir Noche"; la noche retorna a estado `open` y permite correcciones legítimas.
- **TC-H18-04:** Intentar sellar o reabrir noche sin autenticación ni rol administrativo; rechaza con código 403 / redirect.
- **TC-H18-05:** Repetir la solicitud de cierre sobre una noche ya cerrada (idempotencia); el estado se preserva sin duplicar registros.
- **TC-H18-06 (Cypress E2E):** Sellar noche, comprobar bloqueo de inputs y reabrirla; aprueba en `cypress run`.

#### Historia h10 — Vista Táctil de Bodega (8 PH · ROI 0.63)
- **TC-H10-01:** Alternar entre "Tabla Ejecutiva" y "Cuadrícula de Tarjetas Táctiles"; ambas vistas presentan exactamente el mismo stock y marcas.
- **TC-H10-02:** Ajustar stock de bodega usando botones táctiles de paso rápido (-10, -1, +1, +10) desde la tarjeta; el saldo persiste en BD y actualiza el desglose de cajas/sueltas.
- **TC-H10-03:** Cargar catálogo con más de 30 marcas de bebidas; ambas vistas renderizan con fluidez sin pérdida de información.
- **TC-H10-04:** Operar controles en viewport táctil de tablet/móvil con touch targets $\ge 44\text{ px}$.
- **TC-H10-05 (Cypress E2E):** Alternar selector de vista y realizar ajuste táctil; aprueba en `cypress run`.

### 7.3 Escenario de carga (JMeter 5.6)
- 50 usuarios concurrentes, 2 min de ramp-up, 10 min de carga sostenida sobre `/dashboard`, `/closing`, `/products`.
- Umbral de éxito: p95 $\le 1.5$ s y tasa de error $< 1.0\%$.

---

## 8. Notificación y resolución de problemas

Defectos clasificados bajo norma IEEE 1044:
- **S1 (Crítico):** Bloqueo total o corrupción de caja/arqueo. SLA respuesta: $< 2$ h, resolución: $< 12$ h.
- **S2 (Mayor):** Error de cálculo en indicadores o falla en bloqueo de noche. SLA respuesta: $< 4$ h, resolución: $< 24$ h.
- **S3 (Menor):** Inconsistencia visual o tipográfica leve. SLA respuesta: $< 8$ h, resolución: $< 48$ h.
- **S4 (Trivial):** Sugerencia de diseño o mejora estética diferible.

---

## 9. Herramientas, técnicas y metodologías

- **Lenguaje y Framework:** PHP 8.2+ / Laravel 11/12
- **Pruebas Automatizadas:** PHPUnit 11, Pest PHP, Cypress 14
- **Pruebas de Desempeño:** Apache JMeter 5.6
- **Control de Calidad y Formato:** Laravel Pint (PSR-12), Husky, Commitlint
- **CI/CD:** GitHub Actions

---

## 10. Control de código y configuración

- Política de ramas: GitHub Flow (`main` protegida, incrementos en branches temáticas).
- Formato de commits estandarizado bajo Conventional Commits.
- Tagging de versión al cierre: `v4.0.0-sprint4`.

---

## 11. Control de registros y mantenimiento

Archivado de evidencias en el repositorio:
- Informes de ejecución de pruebas: `docs/EVIDENCIAS_SPRINT_4.docx`
- Capturas de pantalla E2E: `cypress/screenshots/sprint4/`
- Reportes de rendimiento: `jmeter/sprint4_results.jtl`

---

## 12. Prácticas de entrega y despliegue

- Pipeline automatizado en `.github/workflows/ci.yml`.
- Empaquetamiento mediante Dockerfile multi-stage.
- Script de despliegue local rápido: `2_INICIAR_TIO_CHU.bat`.

---

## 13. Formación y entrenamiento

- Capacitación a Don Ludo: Lectura ejecutiva del Dashboard en 5 segundos y validación del Balance Express sin scroll.
- Capacitación al encargado de bodega: Conteo y auditoría ágil con la interfaz táctil de tarjetas.

---

## 14. Gestión de riesgos de calidad

| ID | Riesgo | Prob. | Imp. | Mitigación en Sprint 4 |
|---|---|:---:|:---:|---|
| **R-01** | Descuadre entre efectivo reportado y ventas en barras | 2 | 3 | Validar fórmula matemática en servicio centralizado cruzando ingresos y salidas reales. |
| **R-02** | Ediciones indebidas tras sellar la noche | 2 | 3 | Chequeos obligatorios `!$session->isOpen()` en controladores y deshabilitación visual en UI. |
| **R-03** | Sobrecarga de consultas en el Dashboard | 2 | 2 | Consultas Eloquent optimizadas y pre-cálculo en tabla `cash_closings`. |
| **R-04** | Dificultad de navegación táctil con $> 30$ marcas | 1 | 2 | Cuadrícula CSS Grid responsive con filtros rápidos por categoría y buscador instantáneo. |

---

## 15. Aprobación y firmas del SQAP

El presente plan ha sido revisado, validado y aprobado:

```
____________________________          ____________________________          ____________________________
   Félix Maturano Zárate                      André Clarós                     Ing. Carlos Montellano
Líder de Proyecto / Auditor SQA       Ingeniero de Desarrollo y Pruebas       Docente Asignatura COM450
          EQ-LAB07                                EQ-LAB07                              USFX
```

---

## 16. Procedimiento para cambios y anexos

### Anexo 1: Plantilla de reporte de defectos
Formato para registro de incidentes en testing funcional:
`[DEF-S4-XXX] | Historia: hX | Severidad: S1-S4 | Pasos | Esperado | Obtenido | Evidencia | SLA`

### Anexo 2: Matriz de trazabilidad Sprint 4
- **h7:** SRS Req. Balance -> `CashClosingController` / `NightSessionService` -> `TC-H7-01..04` -> `TC-H7-05`
- **h8:** SRS Req. Arqueo -> `CashClosingController` / `DashboardController` -> `TC-H8-01..04` -> `TC-H8-05`
- **h9:** SRS Req. Dashboard -> `DashboardController` / `NightSessionService` -> `TC-H9-01..03` -> `TC-H9-05`
- **h18:** SRS Req. Cierre Seguro -> `CashClosingController` -> `TC-H18-01..05` -> `TC-H18-06`
- **h10:** SRS Req. Vista Táctil -> `ProductController` / `views/products` -> `TC-H10-01..04` -> `TC-H10-05`

### Anexo 3: Plan de rendimiento JMeter
Archivo `jmeter/sprint4_performance.jmx`, 50 hilos concurrentes, 120s ramp-up, umbral p95 $\le 1.5$s.

