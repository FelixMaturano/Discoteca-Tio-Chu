Cypress.Commands.add('loginAsAdmin', () => {
    const adminEmail = Cypress.expose('adminEmail');

    if (!adminEmail) {
        throw new Error('Cypress admin email is not configured.');
    }

    return cy.env(['adminPassword']).then(({ adminPassword }) => {
        if (!adminPassword) {
            throw new Error('Cypress admin password is not configured.');
        }

        cy.session(['admin-user', adminEmail], () => {
            cy.visit('/login');
            cy.get('#email').should('be.visible').type(adminEmail, { force: true });
            cy.get('#password').should('be.visible').type(adminPassword, { log: false, force: true });
            cy.get('#submit-btn').should('be.visible').click({ force: true });
            cy.location('pathname', { timeout: 20000 }).should('eq', '/dashboard');
        }, { cacheAcrossSpecs: true });
        cy.visit('/dashboard');
        cy.location('pathname', { timeout: 20000 }).should('eq', '/dashboard');
    });
});