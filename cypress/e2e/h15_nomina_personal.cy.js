describe('H15 - Gestion de Nomina y Personal', () => {
    beforeEach(() => {
        cy.loginAsAdmin();
    });

    it('crea los cuatro cargos canonicos, edita jornal y elimina personal', () => {
        const staffRecords = [
            { suffix: 'mesero', role: 'MESERO', wage: '145' },
            { suffix: 'bartender', role: 'BARTENDER', wage: '155' },
            { suffix: 'seguridad', role: 'SEGURIDAD', wage: '165' },
            { suffix: 'limpieza', role: 'LIMPIEZA', wage: '135' },
        ];
        const staffIds = {};

        cy.intercept('POST', '/staff').as('createStaff');
        cy.wrap(staffRecords).each(({ suffix, role, wage }) => {
            cy.visit('/staff/create');
            cy.get('#name').clear().type(`E2E ${suffix}`);
            cy.get('#role').select(role).should('have.value', role);
            cy.get('#assigned_bar').select('General');
            cy.get('#default_pay').clear().type(wage);
            cy.get('#friday_pay').clear().type(wage);
            cy.get('[name="works_friday"]').check();
            cy.get('[name="works_saturday"]').check();
            cy.get('[name="works_sunday"]').check();
            cy.contains('button', 'Guardar Personal').click();

            cy.wait('@createStaff').its('response.statusCode').should('eq', 302);
            cy.location('pathname').should('eq', '/staff');
            cy.visit('/staff?day=todos');
            cy.get(`.staff-table-row[data-name="e2e ${suffix}"]`).filter(':visible').should('exist');
        });

        cy.visit('/staff?day=todos');
        cy.get('.staff-table-row[data-name="e2e mesero"]')
            .filter(':visible')
            .find('button')
            .invoke('attr', 'onclick')
            .then((handler) => {
                staffIds.mesero = Number(handler.match(/openEditStaffDrawer\(\s*(\d+)/)[1]);
            });
        cy.get('.staff-table-row[data-name="e2e seguridad"]')
            .filter(':visible')
            .find('button')
            .invoke('attr', 'onclick')
            .then((handler) => {
                staffIds.seguridad = Number(handler.match(/openEditStaffDrawer\(\s*(\d+)/)[1]);
            });

        cy.then(() => {
            cy.intercept('POST', `**/staff/${staffIds.mesero}`).as('updateStaff');
            cy.visit(`/staff/${staffIds.mesero}/edit`);
            cy.get('#name').clear().type('E2E mesero actualizado');
            cy.get('#role').select('MESERO');
            cy.get('#default_pay').clear().type('175');
            cy.get('#friday_pay').clear().type('175');
            cy.get('#is_active').select('1');
            cy.contains('button', 'Guardar Cambios').click();

            cy.wait('@updateStaff').then(({ request, response }) => {
                expect(request.method).to.eq('POST');
                expect(response.statusCode).to.eq(302);
            });
            cy.location('pathname').should('eq', '/staff');
            cy.visit('/staff?day=todos');
            cy.get('.staff-table-row[data-name="e2e mesero actualizado"]')
                .filter(':visible')
                .should('contain.text', 'MESERO')
                .and('contain.text', '175.00');
        });

        cy.then(() => {
            cy.intercept('POST', `**/staff/${staffIds.seguridad}`).as('deleteStaff');
            cy.visit(`/staff/${staffIds.seguridad}/edit`);
            cy.on('window:confirm', () => true);
            cy.contains('button', 'Eliminar Personal').click();
            cy.wait('@deleteStaff').then(({ request, response }) => {
                expect(request.method).to.eq('POST');
                expect(response.statusCode).to.eq(302);
            });
            cy.location('pathname').should('eq', '/staff');
            cy.visit('/staff?day=todos');
            cy.get('.staff-table-row[data-name="e2e seguridad"]').should('not.exist');
        });

        staffRecords.filter(({ suffix }) => ['bartender', 'limpieza'].includes(suffix)).forEach(({ suffix, role }) => {
            cy.get(`.staff-table-row[data-name="e2e ${suffix}"]`)
                .filter(':visible')
                .should('contain.text', role);
        });
    });
});