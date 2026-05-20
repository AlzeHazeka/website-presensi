export function route(name, params = undefined) {
    if (typeof window !== 'undefined' && typeof window.route === 'function') {
        return window.route(name, params);
    }

    throw new Error('Ziggy route() helper is not available. Pastikan @routes ada di root view Inertia.');
}

