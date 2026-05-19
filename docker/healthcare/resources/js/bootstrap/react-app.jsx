import React from 'react';

import ReactDOM from 'react-dom/client';

import PatientVitalsChart
    from '../react/components/PatientVitalsChart';

const registry = {
    PatientVitalsChart,
};

document.querySelectorAll('[data-react-component]')
    .forEach((element) => {

        const componentName =
            element.dataset.reactComponent;

        const Component =
            registry[componentName];

        if (!Component) {
            return;
        }

        const props =
            JSON.parse(
                element.dataset.props || '{}'
            );

        ReactDOM.createRoot(element)
            .render(
                <Component {...props} />
            );
    });