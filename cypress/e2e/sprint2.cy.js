describe('Sprint 2 - Historias H13, H14, H6 y H4', () => {
    it('certifica nómina automática, comisión de tarjeta, cobro QR y conteo de combos', () => {
        cy.loginAsAdmin();
        cy.visit('/sessions/create');
        cy.get('#session_date').clear().type('2026-10-23').trigger('change');
        cy.get('#detected_day_display').should('have.text', 'Viernes');
        cy.get('#pos_commission_rate').clear().type('0.013');

        cy.intercept('POST', '/sessions').as('openSprint2Night');
        cy.contains('button', 'Aperturar Noche').click();
        cy.wait('@openSprint2Night').its('response.statusCode').should('eq', 302);

        cy.visit('/sessions');
        cy.get('[data-session-row]')
            .should('have.length', 1)
            .invoke('attr', 'data-session-row')
            .then((id) => {
                const sessionId = id;

                cy.visit(`/closing?session_id=${sessionId}`);
                cy.contains('button', 'Ver lista de personal y pagos').click();
                cy.get('#drawer-staff-list')
                    .find('.drawer-staff-item[data-search*="kelly"]')
                    .should('contain.text', 'KELLY')
                    .and('contain.text', 'Bs.100.00');

                cy.visit(`/invoices?session_id=${sessionId}`);
                cy.get('#amount').closest('form').within(() => {
                    cy.get('#amount').type('200.00');
                    cy.get('#toggle-notes-btn').click();
                    cy.get('#notes').type('Voucher E2E H13 Sprint2');

                    cy.intercept('POST', '/invoices').as('createCardVoucher');
                    cy.contains('button', 'Registrar Factura').click();
                });

                cy.wait('@createCardVoucher').its('response.statusCode').should('eq', 302);
                cy.get('#invoice-row-1')
                    .should('contain.text', 'Bs. 200.00')
                    .and('contain.text', 'Bs. 2.60')
                    .and('contain.text', 'Bs. 197.40')
                    .and('contain.text', 'Voucher E2E H13 Sprint2');

                cy.visit(`/qrs?session_id=${sessionId}`);
                cy.get('input[name="bank_app"][value="YASTA"]')
                    .should('exist')
                    .closest('label')
                    .find('img[alt="Yasta"]')
                    .should('be.visible');
                cy.get('input[name="bank_app"][value="YAPE"]')
                    .should('exist')
                    .closest('label')
                    .find('img[alt="Yape"]')
                    .should('be.visible');

                cy.get('input[name="bank_app"][value="YAPE"]').check({ force: true });
                cy.get('#cobrante-trigger').click();
                cy.get('.cobrante-item[data-name="KELLY"]').click();


                cy.get('#amount').click('right').type('82.50');
                cy.intercept('POST', '/qrs').as('createQrCharge');
                cy.contains('button', 'Cobrar').click();

                cy.wait('@createQrCharge').then(({ request, response }) => {
                    expect(response.statusCode).to.eq(302);
                    expect(JSON.stringify(request.body)).to.include('E2E-H14-YAPE-20261023');
                    expect(JSON.stringify(request.body)).to.include('YAPE');
                });
                cy.get('tr[id^="qr-row-"]')
                    .should('contain.text', 'Yape')
                    .and('contain.text', 'Bs. 82.50')
                    .find('img[alt="Yape"]')
                    .should('be.visible');

                cy.intercept('POST', '/sales/bulk-update').as('saveBarSales');
                cy.visit(`/sales?session_id=${sessionId}&bar=${encodeURIComponent('Barra Principal')}`);

                cy.get('#sales-form tr.liquor-row[data-product-id="20"]').within(() => {
                    cy.get('.input-units').clear().type('7').blur();
                    cy.get('.input-saldo').clear().type('2').blur();
                });
                cy.get('#sales-form tr.mixer-row[data-product-id="26"]').within(() => {
                    cy.get('.input-packages').clear().type('2').blur();
                    cy.get('.input-units').clear().type('1').blur();
                    cy.get('.input-saldo').clear().type('2').blur();
                });

                cy.get('#sales-form tr.liquor-row[data-product-id="20"] .cell-vendido')
                    .should('have.text', '5');
                cy.get('#sales-form tr.mixer-row[data-product-id="26"] .cell-included')
                    .should('have.text', '10');
                cy.get('#sales-form tr.mixer-row[data-product-id="26"] .cell-extras')
                    .should('have.text', '1');

                cy.wait('@saveBarSales').its('response.statusCode').should('eq', 200);
                cy.get('#sales-autosave-status').should('have.text', 'Guardado');

                cy.visit(`/sales?session_id=${sessionId}&bar=${encodeURIComponent('Barra Principal')}`);
                cy.get('#sales-form tr.liquor-row[data-product-id="20"] .cell-vendido')
                    .should('have.text', '5');
                cy.get('#sales-form tr.mixer-row[data-product-id="26"] .cell-included')
                    .should('have.text', '10');
                cy.get('#sales-form tr.mixer-row[data-product-id="26"] .cell-extras')
                    .should('have.text', '1');
            });
    });
});
