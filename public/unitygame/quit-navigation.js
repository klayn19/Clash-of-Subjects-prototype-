(function () {
    const originalOpen = window.open.bind(window);
    const appBasePath = window.location.pathname
        .replace(/\/unitygame\/(?:gameplay\.php|index\.html)?$/i, '/')
        .replace(/\/gameplay(?:\.php)?\/?$/i, '/');
    const normalizedBasePath = appBasePath.endsWith('/') ? appBasePath : appBasePath + '/';
    const dashboardUrl = new URL(normalizedBasePath + 'student/dashboard', window.location.origin).href;

    window.open = function (url, ...args) {
        if (typeof url === 'string') {
            try {
                const target = new URL(url, window.location.href);
                const targetPath = target.pathname.replace(/\/+$/, '');

                if (targetPath.endsWith('/student/dashboard')) {
                    window.location.assign(dashboardUrl);
                    return null;
                }
            } catch (error) {
                console.warn('Unable to resolve Unity navigation target:', error);
            }
        }

        return originalOpen(url, ...args);
    };
})();
