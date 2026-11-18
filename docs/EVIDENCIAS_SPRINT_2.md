# Evidencias de Calidad — Sprint 2

**Sistema:** Tío Chu App  
**Fuente de reconstrucción:** historial Git, suites PHPUnit/Feature y Cypress versionadas. No se inventan actas, firmas ni tiempos de ejecución.

## 1. Implementación y pruebas automatizadas

El commit [`11bb556`](https://github.com/FelixMaturano/Discoteca-Tio-Chu/commit/11bb556) registra pruebas unitarias y de integración para H13, H14, H6 y H4, además de aplicar Pint. El cambio agrega `H4BarLeftoversAndCombosTest.php` y `H6AutomaticPayrollTest.php`, amplía `QrPaymentTest.php` y agrega pruebas unitarias de facturas en `InvoiceTest.php`.

Los casos existentes se corresponden con:

- **H4:** sobrantes de barra y combos — `tests/Feature/H4BarLeftoversAndCombosTest.php`.
- **H6:** nómina automática — `tests/Feature/H6AutomaticPayrollTest.php`.
- **H13/H14:** pagos QR / facturación — suites `QrPaymentTest.php` e `InvoiceTest.php`; la prueba de facturas está en `tests/Unit/InvoiceTest.php`.

## 2. Evidencia E2E

El commit [`a2b97ac`](https://github.com/FelixMaturano/Discoteca-Tio-Chu/commit/a2b97ac) agrega `cypress/e2e/sprint2.cy.js` para certificar flujos E2E relacionados con H13, H14, H6 y H4. El archivo y sus selectores están versionados; su existencia demuestra automatización definida, no por sí sola una ejecución aprobada en este cierre.

## 3. Evidencia de rendimiento

El commit [`e4900b0`](https://github.com/FelixMaturano/Discoteca-Tio-Chu/commit/e4900b0) registra la configuración de las pruebas de rendimiento JMeter con 50 usuarios según el SQAP de ese momento. Los resultados de carga actuales, su JTL y sus límites se documentan en el [SQAP Maestro General v4.0](SQAP-Maestro-General-v4.0.md), no en esta evidencia histórica de Sprint 2.

## 4. Trazabilidad Git

| Commit | Evidencia verificable |
|---|---|
| `11bb556` | Pruebas unitarias/Feature H13, H14, H6, H4 y aplicación de Pint. |
| `a2b97ac` | Especificación Cypress `sprint2.cy.js`. |
| `e4900b0` | Plan y resultados JMeter registrados en el historial del proyecto. |

No se dispone en estos commits de un acta firmada ni de un reporte de ejecución Cypress de Sprint 2, por lo que no se atribuyen dichos artefactos.
