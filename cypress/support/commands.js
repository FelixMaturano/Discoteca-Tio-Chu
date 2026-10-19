Cypress.Commands.add('loginAsAdmin', () => {
    cy.visit('/login');
    cy.get('#email').type('DonLudo@gmail.com');
    cy.get('#password').type('tiochu123', { log: false });
    cy.get('#submit-btn').click();
    cy.location('pathname', { timeout: 20000 }).should('eq', '/dashboard');
});