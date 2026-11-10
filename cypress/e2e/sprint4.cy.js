function openNight(date) {
    cy.visit('/sessions/create');
    cy.get('#session_date').clear().type(date).trigger('change');
    cy.get('#pos_commission_rate').clear().type('0.05');
    cy.get('#opening_cash').clear().type('150.00');
    cy.intercept('POST', '/sessions').as(`openNight${date.replaceAll('-', '')}`);
    cy.contains('button', 'Aperturar Noche').click();
    cy.wait(`@openNight${date.replaceAll('-', '')}`).its('response.statusCode').should('eq', 302);

    return cy.visit('/sessions')
        .get(`tr[data-session-row] input[data-date^="${date}"]`)
        .should('have.length', 1)
        .closest('tr[data-session-row]')
        .invoke('attr', 'data-session-row');
}

function closeNight(sessionId) {
    cy.on('window:confirm', () => true);
    cy.visit(`/closing?session_id=${sessionId}`);
    cy.intercept('POST', `**/closing/${sessionId}/close`).as(`closeNight${sessionId}`);
    cy.get('[data-cy="btn-close-night"]').click();
    cy.wait(`@closeNight${sessionId}`).its('response.statusCode').should('eq', 302);
}

describe('Sprint 4 - Historias H7, H8, H9, H18 y H10 (Discoteca Tío Chu)', () => {
    beforeEach(() => {
        cy.clearCookies();
        cy.loginAsAdmin();
    });

    it('TC-H7-05 (H7): Balance Express muestra ingresos, egresos y saldo neto en panel sin scroll', () => {
        openNight('2026-11-10').then((sessionId) => {
            cy.visit(`/closing?session_id=${sessionId}`);
            cy.get('[data-cy="balance-express-panel"]').should('be.visible');
            cy.get('[data-cy="balance-neto-caja"]').should('be.visible').and('not.be.empty');
            cy.get('[data-cy="balance-total-ingresos"]').should('be.visible').and('not.be.empty');
            cy.get('[data-cy="balance-total-egresos"]').should('be.visible').and('not.be.empty');
            cy.contains('Tarjetas (Neto)').should('be.visible');
            cy.contains('QR Yasta').should('be.visible');
            cy.contains('QR Yape').should('be.visible');
            cy.contains('Efectivo en Mano').should('be.visible');
            cy.screenshot('sprint4/H7-balance-express');

            closeNight(sessionId);
        });
    });

    it('TC-H8-05 (H8): Arqueo de Efectivo Físico calcula diferencia en mano y actualiza estado dinámico', () => {
        openNight('2026-11-11').then((sessionId) => {
            cy.visit(`/closing?session_id=${sessionId}`);
            cy.get('[data-cy="card-arqueo-fisico"]').should('be.visible');
            cy.get('[data-cy="expected-physical-cash"]').should('be.visible');
            cy.get('[data-cy="reported-physical-cash"]').should('be.visible');

            // Actualizar efectivo contado en mano
            cy.get('[data-cy="input-reported-cash"]').clear().type('250.00');
            cy.intercept('POST', '**/reported-cash').as('updateReportedCash');
            cy.get('[data-cy="btn-save-reported-cash"]').click();
            cy.wait('@updateReportedCash').its('response.statusCode').should('be.oneOf', [200, 302]);

            // Comprobar que el valor se actualizó y se muestra la diferencia
            cy.visit(`/closing?session_id=${sessionId}`);
            cy.get('[data-cy="reported-physical-cash"]').should('contain.text', '250.00');
            cy.get('[data-cy="arqueo-difference-container"]').should('be.visible');
            cy.screenshot('sprint4/H8-arqueo-efectivo-fisico');

            closeNight(sessionId);
        });
    });

    it('TC-H9-05 (H9): Dashboard Ejecutivo visualiza los 4 KPIs en tiempo real con datos de sesión', () => {
        openNight('2026-11-12').then((sessionId) => {
            cy.visit(`/dashboard?session_id=${sessionId}`);
            cy.get('[data-cy="kpi-venta-barras"]').should('be.visible').and('not.be.empty');
            cy.get('[data-cy="kpi-tarjetas-neto"]').should('be.visible').and('not.be.empty');
            cy.get('[data-cy="kpi-cobros-qr"]').should('be.visible').and('not.be.empty');
            cy.get('[data-cy="kpi-efectivo-caja"]').should('be.visible').and('not.be.empty');
            cy.contains('Venta en Barras').should('be.visible');
            cy.contains('Tarjetas Neto Banco').should('be.visible');
            cy.contains('Cobros QR').should('be.visible');
            cy.contains('Efectivo en Caja').should('be.visible');
            cy.screenshot('sprint4/H9-dashboard-ejecutivo');

            closeNight(sessionId);
        });
    });

    it('TC-H18-06 (H18): Cierre Seguro de Noche bloquea mutaciones, muestra indicador y permite reapertura', () => {
        openNight('2026-11-13').then((sessionId) => {
            cy.visit(`/closing?session_id=${sessionId}`);

            // 1. Sellar la noche
            cy.on('window:confirm', () => true);
            cy.intercept('POST', `**/closing/${sessionId}/close`).as('closeNightStep1');
            cy.get('[data-cy="btn-close-night"]').click();
            cy.wait('@closeNightStep1').its('response.statusCode').should('eq', 302);

            // 2. Verificar estado cerrado y bloqueo
            cy.visit(`/closing?session_id=${sessionId}`);
            cy.get('[data-cy="badge-night-closed-indicator"]').should('be.visible');
            cy.get('[data-cy="badge-arqueo-locked"]').should('be.visible');
            cy.get('[data-cy="btn-reopen-night"]').should('be.visible');
            cy.screenshot('sprint4/H18-noche-cerrada-bloqueada');

            // 3. Reabrir la noche
            cy.intercept('POST', `**/closing/${sessionId}/reopen`).as('reopenNightStep');
            cy.get('[data-cy="btn-reopen-night"]').click();
            cy.wait('@reopenNightStep').its('response.statusCode').should('eq', 302);

            // 4. Verificar reapertura exitosa
            cy.visit(`/closing?session_id=${sessionId}`);
            cy.get('[data-cy="btn-close-night"]').should('be.visible');
            cy.screenshot('sprint4/H18-noche-reabierta');

            // 5. Cerrar definitivamente la noche para no dejarla abierta
            closeNight(sessionId);
        });
    });

    it('TC-H10-05 (H10): Vista Táctil de Bodega alterna entre Cuadrícula y Tabla con ajuste rápido de stock', () => {
        cy.visit('/products');

        // 1. Vista de cuadrícula por defecto
        cy.get('[data-cy="view-cards-container"]').should('be.visible');
        cy.get('[data-cy="view-table-container"]').should('not.be.visible');

        // 2. Alternar a Vista de Tabla
        cy.get('[data-cy="btn-view-table"]').click();
        cy.get('[data-cy="view-table-container"]').should('be.visible');
        cy.get('[data-cy="view-cards-container"]').should('not.be.visible');
        cy.screenshot('sprint4/H10-bodega-vista-tabla');

        // 3. Volver a Vista de Tarjetas Táctiles
        cy.get('[data-cy="btn-view-cards"]').click();
        cy.get('[data-cy="view-cards-container"]').should('be.visible');
        cy.get('[data-cy="view-table-container"]').should('not.be.visible');

        // 4. Realizar un ajuste ágil de stock (+1)
        cy.get('[data-cy="view-cards-container"] .product-item').first().within(() => {
            cy.get('[data-cy="stock-input-cards"]').invoke('val').then((initialStock) => {
                const start = parseInt(initialStock, 10);
                cy.intercept('PATCH', '**/products/*/quick-stock').as('quickStockStep');
                cy.get('[data-cy="step-stock-plus-1"]').click();
                cy.wait('@quickStockStep').its('response.statusCode').should('eq', 200);
                cy.get('[data-cy="stock-input-cards"]').should('have.value', (start + 1).toString());
            });
        });
        cy.screenshot('sprint4/H10-bodega-tarjetas-tactiles');
    });
});
