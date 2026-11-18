# SQAP Maestro General v4.0

**Sistema:** Tío Chu App — Gestión Operativa Nocturna  
**Propósito:** consolidar criterios de aseguramiento y resultados de pruebas de Sprint 1 a Sprint 4 y cierre de auditoría INF-AUD-TC-2026-01.  
**Vigencia:** métricas indicadas como cierre vigente corresponden a los artefactos disponibles en el repositorio al preparar este documento.

## 1. Alcance y referencias

El SQAP cubre requisitos, calidad de código, pruebas automatizadas, E2E, rendimiento, seguridad HTTP y despliegue. Se apoya en [README](../README.md), [SRS](SRS.md), [SAD](SAD.md), [Manual de Usuario](manual/MANUAL_USUARIO.md), [evidencias Sprint 1](EVIDENCIAS_SPRINT_1.md), [evidencias Sprint 2](EVIDENCIAS_SPRINT_2.md), y la suite `tests/` / `cypress/e2e/`.

## 2. Estrategia de aseguramiento

| Nivel | Herramientas y evidencia | Propósito |
|---|---|---|
| Estilo y análisis estático | Laravel Pint; PHPStan nivel 5 sobre `app/` | Detectar desviaciones de formato y problemas estáticos. |
| Unitario/Feature | PHPUnit 11, Laravel y SQLite de pruebas | Validar modelos, controladores, autorización, cálculos e integración HTTP. |
| E2E | Cypress en `cypress/e2e/` | Automatizar flujos críticos desde navegador con selectores `data-cy`. |
| Rendimiento | Apache JMeter, plan y JTL en `jmeter/` | Medir respuesta concurrente de login y vistas operativas. |
| Dependencias/CI | Composer Audit y workflow de GitHub Actions | Verificar dependencias y ejecutar las puertas de calidad requeridas. |

## 3. Criterios y resultados de cierre

### 3.1 Pruebas automatizadas

**PHPUnit:** 178 tests ejecutados, 984 aserciones y 0 fallos (**100% aprobados**), según el cierre reportado para `php artisan test`.

### 3.2 Cobertura

Fuente: [`coverage/clover.xml`](../coverage/clover.xml).

| Métrica | Valor |
|---|---:|
| Archivos analizados | 37 |
| Sentencias/líneas cubiertas | 2,419 de 3,023 |
| Cobertura | 80.02% |
| Umbral | `>= 80.00%` |
| Estado D-10 | **CERRADO — CUMPLE** |

### 3.3 Rendimiento

Fuentes: [`jmeter/sprint4_results.jtl`](../jmeter/sprint4_results.jtl) e informe generado en `jmeter/reporte_html/statistics.json`.

| Métrica | Resultado | Umbral | Estado |
|---|---:|---:|---|
| Usuarios concurrentes | 50 | 50 | Ejecutado |
| Peticiones/muestras | 7,600 | — | Medido |
| p95 | 727.95 ms | `<= 1,500 ms` | Cumple |
| Promedio | 326.56 ms | Informativo | Medido |
| Throughput | 120.90 req/s | `>= 30 req/s` | Cumple |
| Muestras fallidas | 3 / 7,600 = 0.04% | `< 1%` | Cumple |
| APDEX | 0.909 (valor reportado 0.909276) | Informativo | Medido |

Las tres muestras fallidas fueron `DurationAssertion` por superar 1,500 ms, aunque devolvieron HTTP 200; no se clasifican como respuestas HTTP de error. El resultado sí cuenta estas muestras como fallidas en el porcentaje anterior.

**Estado D-06: CERRADO — CUMPLE** con los umbrales registrados.

### 3.4 Seguridad y producción

- La política CSP está activa; permite assets compilados y las fuentes necesarias. Los hosts Vite/HMR se agregan solamente en entorno local.
- `URL::forceScheme('https')` se aplica en producción.
- `public/hot` está excluido del contexto Docker para impedir que el despliegue apunte accidentalmente al servidor Vite.
- HSTS se añade en respuestas HTTPS. La política de proxies se configura mediante `TRUSTED_PROXIES`; debe limitarse a proxies/CIDR administrados.
- Docker ejecuta migraciones y seed antes de iniciar Apache. `APP_KEY` y `ADMIN_PASSWORD` deben configurarse como secretos de despliegue.
- Esta evidencia verifica archivos y artefactos del repositorio; no afirma una prueba en vivo del servicio Render.

## 4. Usabilidad y pruebas E2E (D-09)

Hay automatización E2E definida en `cypress/e2e/` para apertura de sesión, despacho/stock, ventas, nómina y Sprint 4 (dashboard, arqueo y cierre). Los archivos actuales prueban flujos críticos; no se usa la mera existencia de una especificación para afirmar una ejecución aprobada. Las ejecuciones E2E dirigidas reportadas por el proyecto se mantienen como evidencia automatizada separada.

La aplicación presencial de encuestas SUS y NASA-TLX con **5 operadores** queda programada como seguimiento post-auditoría. No se dispone de actas, formularios firmados ni resultados presenciales en el repositorio; no se inventan.

## 5. Trazabilidad de cierre de hallazgos

| Hallazgo | Estado | Evidencia / criterio |
|---|---|---|
| D-06 — Rendimiento | **CERRADO** | 50 usuarios; 7,600 muestras; p95 727.95 ms; throughput 120.90 req/s; error 0.04%; APDEX 0.909. |
| D-10 — Cobertura | **CERRADO** | Clover: 2,419/3,023, 80.02%, 37 archivos; supera el umbral 80%. |
| D-15 — Documentación técnica | **CERRADO** | [README](../README.md), [SRS](SRS.md), [SAD](SAD.md), [Manual](manual/MANUAL_USUARIO.md), [evidencias Sprint 1](EVIDENCIAS_SPRINT_1.md) y [Sprint 2](EVIDENCIAS_SPRINT_2.md). |
| D-16 — Documentación y entorno operativo | **CERRADO** | Guía reproducible en [README](../README.md), stack/deployment en [SAD](SAD.md), pasos operativos en [Manual](manual/MANUAL_USUARIO.md) y variables sensibles descritas para configuración externa. |

## 6. Higiene de artefactos

- `jmeter/reporte_html/` y archivos temporales de editor están ignorados por Git.
- `jmeter/sprint4_results.jtl`, `coverage/clover.xml` y `docs/` no deben ignorarse: son evidencia/documentación.
- Las capturas duplicadas con `(1)` o `(2)` no estaban presentes en `cypress/screenshots/` al hacer la inspección; por ello no se borró ninguna.

## 7. Limitaciones y seguimiento

1. El porcentaje de disponibilidad no está cuantificado por un SLO del repositorio; debe definirse con el responsable de operación.
2. La ejecución de encuestas SUS/NASA-TLX con cinco operadores es seguimiento post-auditoría pendiente.
3. El SQLite de Render necesita almacenamiento persistente; para un servicio efímero se debe emplear una base administrada o volumen persistente.
4. Los resultados históricos de `docs/EVIDENCIAS_SPRINT_4.md` son un snapshot anterior; los valores de este documento son las métricas de cierre indicadas en los artefactos actuales.
