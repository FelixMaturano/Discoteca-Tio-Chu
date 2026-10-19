describe('H12 - Apertura de Inventario en Barras', () => {
    beforeEach(() => {
        cy.loginAsAdmin();
    });

    it('registra y traslada stock de apertura a las tres barras', () => {
        cy.intercept('PATCH', '**/products/1/quick-stock').as('setWarehouseStock');
        cy.visit('/products');
        cy.get('#stock-input-cards-1').clear().type('6').blur();
        cy.wait('@setWarehouseStock').its('response.body.stock_warehouse').should('eq', 6);

        cy.visit('/sessions/create');
        cy.get('#session_date').clear().type('2026-10-20').trigger('change');
        cy.get('#pos_commission_rate').clear().type('0.013');
        cy.intercept('POST', '/sessions').as('openInventoryNight');
        cy.contains('button', 'Aperturar Noche').click();
        cy.wait('@openInventoryNight').its('response.statusCode').should('eq', 302);

        cy.visit('/sessions');
        cy.get('[data-session-row]').should('have.length', 1).invoke('attr', 'data-session-row').then((sessionId) => {
            const dispatches = [
                { bar: 'Barra Principal', units: '2' },
                { bar: 'Barra Subterráneo', units: '1' },
                { bar: 'Tienda', units: '3' },
            ];

            dispatches.forEach(({ bar, units }) => {
                cy.intercept('POST', '**/bar-inventory/bulk-update').as('saveBarInventory');
                cy.visit(`/bar-inventory?session_id=${sessionId}&bar=${encodeURIComponent(bar)}`);
                cy.get('#bar-inventory-form').should('be.visible');
                cy.get('.inv-row[data-name="singani casa real negro"] .input-add-units')
                    .clear()
                    .type(units)
                    .blur();

                cy.wait('@saveBarInventory').then(({ request, response }) => {
                    expect(request.headers['x-requested-with']).to.eq('XMLHttpRequest');
                    expect(response.statusCode).to.be.oneOf([200, 302]);
                });
                cy.get('#inventory-autosave-status').should('have.text', 'Guardado');
                cy.get('.inv-row[data-name="singani casa real negro"] .input-add-units').should('have.value', units);
            });
        });

        cy.visit('/products');
        cy.get('#stock-input-cards-1').should('have.value', '0');
    });
});