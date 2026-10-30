Cypress.Commands.add('loginAsAdmin', () => {
    cy.session('admin-user', () => {
        cy.visit('/login');
        cy.get('#email').should('be.visible').type('DonLudo@gmail.com', { force: true });
        cy.get('#password').should('be.visible').type('tiochu123', { log: false, force: true });
        cy.get('#submit-btn').should('be.visible').click({ force: true });
        cy.location('pathname', { timeout: 20000 }).should('eq', '/dashboard');
    });
    cy.visit('/dashboard');
    cy.location('pathname', { timeout: 20000 }).should('eq', '/dashboard');
});