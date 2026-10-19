describe('H2 - Apertura de Sesion Nocturna', () => {
    beforeEach(() => {
        cy.loginAsAdmin();
    });

    it('detecta el dia, abre una noche y bloquea una segunda apertura duplicada', () => {
        const sessionDate = '2026-10-23';

        cy.visit('/sessions/create');
        cy.get('#session_date').clear().type(sessionDate).trigger('change');
        cy.get('#detected_day_display').should('have.text', 'Viernes');
        cy.get('#pos_commission_rate').should('have.value', '0.013');
        cy.get('#notes').type('E2E H2 apertura');

        cy.intercept('POST', '/sessions').as('openSession');
        cy.contains('button', 'Aperturar Noche').click();
        cy.wait('@openSession').its('response.statusCode').should('eq', 302);
        cy.location('pathname').should('eq', '/sessions');
        cy.contains('Noche en Curso').parent().should('contain.text', 'Viernes');

        cy.visit('/sessions/create');
        cy.contains('Jornada Anterior Aún Abierta').should('be.visible');
        cy.get('#session_date').clear().type(sessionDate).trigger('change');

        cy.intercept('POST', '/sessions').as('duplicateSession');
        cy.contains('button', 'Aperturar Noche').click();
        cy.wait('@duplicateSession').its('response.statusCode').should('eq', 302);
        cy.location('pathname').should('eq', '/sessions/create');
        cy.contains('Jornada Anterior Aún Abierta').should('be.visible');

        cy.visit('/sessions');
        cy.get('[data-session-row]')
            .should('have.length', 1)
            .and('contain.text', '23/10/2026')
            .and('contain.text', 'Viernes');
    });
});