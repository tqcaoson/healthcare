export function initializePerformanceMonitor() {

    window.addEventListener('load', () => {

        const navigation =
            performance.getEntriesByType(
                'navigation'
            )[0];

        console.log(
            'Page load:',
            navigation.loadEventEnd
        );
    });
}