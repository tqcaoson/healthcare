describe('Login', () => {

    it('logs user in', () => {

        cy.visit('/login');

        cy.get('input[name=email]')
            .type('admin@example.com');

        cy.get('input[name=password]')
            .type('password');

        cy.get('button[type=submit]')
            .click();

        cy.url()
            .should('include', '/dashboard');
    });
});