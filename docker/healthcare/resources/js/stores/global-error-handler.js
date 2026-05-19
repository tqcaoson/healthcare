window.addEventListener('unhandledrejection', (event) => {

    console.error(event.reason);

    alert('Unexpected system error');
});

window.addEventListener('error', (event) => {

    console.error(event.error);

    alert('Application error');
});