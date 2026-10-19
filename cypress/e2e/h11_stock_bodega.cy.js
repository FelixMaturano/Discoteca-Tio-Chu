describe('H11 - Ajuste de Stock en Bodega Central', () => {
    beforeEach(() => {
        cy.loginAsAdmin();
    });

    it('ajusta stock con los cuatro botones y edicion manual AJAX sin recargar', () => {
        cy.intercept('PATCH', '**/products/1/quick-stock').as('stockUpdate');
        cy.visit('/products');
        cy.contains('h1', 'Bodega Central').should('be.visible');

        cy.get('#stock-input-cards-1').clear().type('50').blur();
        cy.wait('@stockUpdate').then(({ request, response }) => {
            expect(request.body.stock).to.eq(50);
            expect(response.statusCode).to.eq(200);
            expect(response.body.stock_warehouse).to.eq(50);
        });
        cy.get('#stock-input-cards-1').should('have.value', '50');

        const adjustments = [
            { title: '-10 unidades', stock: '40' },
            { title: '-1 unidad', stock: '39' },
            { title: '+1 unidad', stock: '40' },
            { title: '+10 unidades', stock: '50' },
        ];

        adjustments.forEach(({ title, stock }) => {
            cy.get('#stock-input-cards-1')
                .closest('.product-item')
                .find(`button[title="${title}"]`)
                .click();
            cy.wait('@stockUpdate').its('response.body.stock_warehouse').should('eq', Number(stock));
            cy.get('#stock-input-cards-1').should('have.value', stock);
        });

        cy.location('pathname').should('eq', '/products');
    });
});