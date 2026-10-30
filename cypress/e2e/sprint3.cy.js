function openNight(date) {
    cy.visit('/sessions/create');
    cy.get('#session_date').clear().type(date).trigger('change');
    cy.get('#pos_commission_rate').clear().type('0.013');
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
    cy.intercept('POST', `**/closing/${sessionId}/close`).as('closeNight');
    cy.contains('button', 'Cerrar Noche Definitiva').click();
    cy.wait('@closeNight').its('response.statusCode').should('eq', 302);
}

function ensurePayrollAttendance(sessionId) {
    cy.get('#payroll-tbody tr[id^="attendance-row-"]').then(($rows) => {
        if ($rows.length > 0) {
            return;
        }

        cy.get(`form[action$="/staff-payments/store"]`)
            .find('select[name="staff_id"] option')
            .contains('KELLY')
            .invoke('val')
            .then((staffId) => {
                cy.get(`form[action$="/staff-payments/store"] select[name="staff_id"]`).select(staffId);
                cy.intercept('POST', '**/staff-payments/store').as('addPayrollAttendance');
                cy.get(`form[action$="/staff-payments/store"]`).submit();
                cy.wait('@addPayrollAttendance').its('response.statusCode').should('eq', 302);
                cy.visit(`/staff-payments?session_id=${sessionId}`);
            });
    });
}

describe('Sprint 3 - Historias H3, H5, H16 y H17', () => {
    beforeEach(() => {
        cy.clearCookies();
        cy.loginAsAdmin();
    });

    it('H3 sincroniza el stock inicial desde el saldo de la noche anterior', () => {
        openNight('2026-10-20').then((previousSessionId) => {
            cy.intercept('POST', '**/bar-inventory/bulk-update').as('savePreviousInventory');
            cy.visit(`/bar-inventory?session_id=${previousSessionId}&bar=${encodeURIComponent('Barra Principal')}`);
            cy.get('.inv-row[data-name="ganesha gin"] .input-init-units')
                .clear()
                .type('14')
                .blur();
            cy.wait('@savePreviousInventory').its('response.statusCode').should('be.oneOf', [200, 302]);

            cy.intercept('POST', '**/sales/bulk-update').as('savePreviousClosing');
            cy.visit(`/sales?session_id=${previousSessionId}&bar=${encodeURIComponent('Barra Principal')}`);
            cy.get('#sales-form tr.liquor-row[data-product-id="20"] .input-saldo')
                .clear()
                .type('14')
                .blur();
            cy.wait('@savePreviousClosing').its('response.statusCode').should('eq', 200);

            closeNight(previousSessionId);
        });

        openNight('2026-10-21').then((currentSessionId) => {
            cy.visit(`/bar-inventory?session_id=${currentSessionId}&bar=${encodeURIComponent('Barra Principal')}`);
            cy.contains('button', 'Traer Sobrante de Ayer').click();
            cy.contains('Saldos de la noche anterior sincronizados correctamente')
                .should('be.visible');
            cy.get('.inv-row[data-name="ganesha gin"] .input-init-pkg')
                .should('have.value', '2');
            cy.get('.inv-row[data-name="ganesha gin"] .input-init-units')
                .should('have.value', '2');
            cy.get('.inv-row[data-name="ganesha gin"] .cell-init-bot')
                .should('have.text', '14');
            cy.screenshot('sprint3/H3-stock-inicial-sincronizado');
            closeNight(currentSessionId);
        });
    });

    it('H5 muestra la liquidación por bartender con ventas, QR, tarjeta y efectivo neto', () => {
        openNight('2026-10-30').then((sessionId) => {
            cy.intercept('POST', '**/sales/bulk-update').as('saveH5Sales');
            cy.visit(`/sales?session_id=${sessionId}&bar=${encodeURIComponent('Barra Principal')}`);
            cy.get('#sales-form tr.liquor-row[data-product-id="20"]').within(() => {
                cy.get('.input-units').clear().type('1');
                cy.get('.input-saldo').clear().type('0').blur();
            });
            cy.wait('@saveH5Sales').its('response.statusCode').should('eq', 200);

            cy.visit(`/invoices?session_id=${sessionId}`);
            cy.get('#amount').closest('form').within(() => {
                cy.get('#amount').type('30.00');
                cy.intercept('POST', '/invoices').as('createH5Card');
                cy.contains('button', 'Registrar Factura').click();
            });
            cy.wait('@createH5Card').its('response.statusCode').should('eq', 302);

            cy.visit(`/qrs?session_id=${sessionId}`);
            cy.get('input[name="bank_app"][value="YASTA"]').check({ force: true });
            cy.get('#cobrante-trigger').click();
            cy.get('.cobrante-item[data-name="KELLY"]').click();

            cy.get('#amount').click('right').type('20.00');
            cy.intercept('POST', '/qrs').as('createH5Qr');
            cy.contains('button', 'Cobrar').click();
            cy.wait('@createH5Qr').its('response.statusCode').should('eq', 302);

            cy.visit(`/closing?session_id=${sessionId}`);
            cy.contains('span', 'Liquidación por bartender').should('be.visible');
            cy.contains('span', 'KELLY').should('be.visible');
            cy.contains('span', 'Efectivo neto').should('be.visible');
            cy.contains('span', 'Bs. 250.00').should('be.visible');
            cy.screenshot('sprint3/H5-liquidacion-por-bartender');
            closeNight(sessionId);
        });
    });

    it('H16 recalcula la planilla y guarda el jornal marcado como pagado', () => {
        openNight('2026-10-23').then((sessionId) => {
            cy.visit(`/staff-payments?session_id=${sessionId}`);
            ensurePayrollAttendance(sessionId);
            cy.get('#payroll-tbody tr[id^="attendance-row-"]').first().as('firstAttendance');
            cy.get('@firstAttendance').find('.pay-amount-input').clear().type('125').trigger('input');
            cy.get('@firstAttendance').find('.is-paid-checkbox').check({ force: true }).trigger('change');
            cy.get('@firstAttendance').find('.pay-amount-input').should('have.value', '125');
            cy.get('@firstAttendance').find('.is-paid-checkbox').should('be.checked');
            cy.get('#kpi-total-planilla').should('not.contain.text', '0.00');
            cy.get('#kpi-total-pagado').should('not.contain.text', '0.00');
            cy.screenshot('sprint3/H16-recalculo-planilla');

            cy.intercept('POST', '**/staff-payments/update').as('saveH16Payroll');
            cy.get('#payroll-form').submit();
            cy.wait('@saveH16Payroll').its('response.statusCode').should('eq', 200);
            cy.get('#btn-save-payroll').should('contain.text', 'Guardado');
            cy.screenshot('sprint3/H16-pago-guardado');
            closeNight(sessionId);
        });
    });

    it('H17 paga un jornal atrasado de forma asíncrona y evita duplicarlo', () => {
        openNight('2026-10-24').then((sessionId) => {
            cy.visit(`/staff-payments?session_id=${sessionId}`);
            ensurePayrollAttendance(sessionId);
            cy.get('#payroll-tbody tr[id^="attendance-row-"]').first().as('firstAttendance');
            cy.get('@firstAttendance').find('.pay-amount-input').clear().type('120').trigger('input');
            cy.intercept('POST', '**/staff-payments/update').as('saveH17Debt');
            cy.get('#payroll-form').submit();
            cy.wait('@saveH17Debt').its('response.statusCode').should('eq', 200);

            closeNight(sessionId);

            cy.visit(`/payment-history?session_id=${sessionId}`);
            cy.get('.worker-card[data-paid="false"]').first().as('debtCard');
            cy.get('@debtCard').should('contain.text', 'Por Pagar');
            cy.get('@debtCard').should('contain.text', 'Pagar Jornal');
            cy.screenshot('sprint3/H17-deuda-pendiente');

            cy.on('window:confirm', () => true);
            cy.intercept('POST', '**/payment-history/pay-single/*').as('payH17Debt');
            cy.get('@debtCard').contains('button', 'Pagar Jornal').click();
            cy.wait('@payH17Debt').then(({ response }) => {
                expect(response.statusCode).to.eq(200);
                expect(response.body.success).to.eq(true);
            });
            cy.reload();
            cy.get('.worker-card[data-paid="true"]')
                .should('exist')
                .first()
                .should('contain.text', 'Pagado')
                .and('contain.text', 'Deuda: Bs. 0.00');
            cy.screenshot('sprint3/H17-deuda-pagada');
        });
    });
});
