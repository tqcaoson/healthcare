export function initializeTheme() {

    const stored = localStorage.getItem('theme');

    if (stored === 'dark') {
        document.documentElement.classList.add('dark');
    }
}

export function toggleTheme() {

    const html = document.documentElement;

    html.classList.toggle('dark');

    localStorage.setItem(
        'theme',
        html.classList.contains('dark')
            ? 'dark'
            : 'light'
    );
}