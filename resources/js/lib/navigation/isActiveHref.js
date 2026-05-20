function normalizePath(input) {
    if (!input) return '';

    try {
        const base = typeof window !== 'undefined' ? window.location.origin : 'http://localhost';
        const url = new URL(input, base);
        const pathname = url.pathname || '';
        if (!pathname) return '';
        if (pathname !== '/' && pathname.endsWith('/')) return pathname.slice(0, -1);
        return pathname;
    } catch {
        const pathOnly = String(input).split('?')[0] || '';
        if (!pathOnly) return '';
        if (pathOnly !== '/' && pathOnly.endsWith('/')) return pathOnly.slice(0, -1);
        return pathOnly;
    }
}

export function isActiveHref(currentUrl, href) {
    if (!href) return false;
    const currentPath = normalizePath(currentUrl);
    const hrefPath = normalizePath(href);
    if (!hrefPath) return false;
    return currentPath === hrefPath || currentPath.startsWith(`${hrefPath}/`);
}

