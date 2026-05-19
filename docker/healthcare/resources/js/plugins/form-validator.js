export function validate(rules, data) {

    const errors = {};

    Object.keys(rules).forEach((field) => {

        const fieldRules = rules[field];

        fieldRules.forEach((rule) => {

            if (
                rule === 'required'
                && !data[field]
            ) {

                errors[field] =
                    `${field} is required`;
            }
            )
        });

    return errors;
}