import './bootstrap';
import '../css/app.css';

import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
// Temporarily using mock i18n for English-only mode
import { MockI18nextProvider } from './i18n/mock';
import { router } from '@inertiajs/react';

// Setup CSRF token for Inertia requests with error handling
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    // Set CSRF token for Inertia router with safety checks
    if (router && router.defaults) {
        if (!router.defaults.headers) {
            router.defaults.headers = {};
        }
        router.defaults.headers['X-CSRF-TOKEN'] = token.content;
        console.log('✅ CSRF token configured for Inertia router');
    } else {
        console.warn('⚠️ Inertia router defaults not available');
    }
    
    // Also set for axios if available
    if (window.axios && window.axios.defaults && window.axios.defaults.headers) {
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
        console.log('✅ CSRF token configured for axios');
    } else {
        console.warn('⚠️ Axios defaults not available');
    }
    
    console.log('✅ CSRF token setup completed:', token.content.substring(0, 10) + '...');
} else {
    console.error('❌ CSRF token not found in meta tags!');
}

// Enhanced 419 error handling with retry logic
router.on('error', async (event) => {
    const response = event.detail.response;
    const visit = event.detail.visit;
    
    if (response?.status === 419) {
        console.log('🔄 419 Page Expired detected - attempting CSRF token refresh');
        
        try {
            // Refresh CSRF token
            if (window.refreshCsrfToken) {
                await window.refreshCsrfToken();
                console.log('✅ CSRF token refreshed - retrying request');
                
                // Update router defaults with new token
                const newToken = document.head.querySelector('meta[name="csrf-token"]')?.content;
                if (newToken && router.defaults?.headers) {
                    router.defaults.headers['X-CSRF-TOKEN'] = newToken;
                }
                
                // For login requests, reload the page instead of retrying
                if (visit?.url?.includes('/login') || window.location.pathname === '/login') {
                    console.log('🔄 Login page CSRF error - reloading page');
                    window.location.reload();
                    return;
                }
                
                // For other requests, try to retry the visit
                if (visit && visit.url) {
                    console.log('🔄 Retrying Inertia visit with refreshed token');
                    router.visit(visit.url, {
                        method: visit.method || 'get',
                        data: visit.data || {},
                        preserveScroll: true,
                        preserveState: true
                    });
                    return;
                }
                
                // Fallback: reload page
                window.location.reload();
            } else {
                console.log('🔄 Refreshing page to get new CSRF token');
                window.location.reload();
            }
        } catch (error) {
            console.log('❌ CSRF refresh failed - reloading page');
            window.location.reload();
        }
    }
    
    // Handle other common errors
    if (response?.status === 401) {
        console.log('🔐 Unauthorized - redirecting to login');
        window.location.href = '/login';
    }
    
    if (response?.status >= 500) {
        console.error('🚨 Server error:', response.status, response.statusText);
    }
});

// Simple route helper until we install Ziggy
window.route = function(name, params = {}) {
    const routes = {
        'login': '/login',
        'register': '/register',
        'dashboard': '/dashboard',
        'users.index': '/users',
        'users.create': '/users/create',
        'users.store': '/users',
        'users.show': (id) => `/users/${id}`,
        'users.edit': (id) => `/users/${id}/edit`,
        'users.update': (id) => `/users/${id}`,
        'users.destroy': (id) => `/users/${id}`,
        'users.toggle-status': (id) => `/users/${id}/toggle-status`,
        'users.reset-password': (id) => `/users/${id}/reset-password`,
        'departments.index': '/departments',
        'departments.create': '/departments/create',
        'departments.store': '/departments',
        'departments.show': (id) => `/departments/${id}`,
        'departments.edit': (id) => `/departments/${id}/edit`,
        'departments.update': (id) => `/departments/${id}`,
        'departments.destroy': (id) => `/departments/${id}`,
        'departments.toggle-status': (id) => `/departments/${id}/toggle-status`,
        'departments.move': (id) => `/departments/${id}/move`,
        'roles.index': '/roles',
        'roles.create': '/roles/create',
        'roles.store': '/roles',
        'roles.show': (id) => `/roles/${id}`,
        'roles.edit': (id) => `/roles/${id}/edit`,
        'roles.update': (id) => `/roles/${id}`,
        'roles.destroy': (id) => `/roles/${id}`,
        'roles.toggle-status': (id) => `/roles/${id}/toggle-status`,
        'roles.duplicate': (id) => `/roles/${id}/duplicate`,
        'audit-logs.index': '/audit-logs',
        'audit-logs.show': (id) => `/audit-logs/${id}`,
        'audit-logs.dashboard': '/audit-logs/dashboard',
        'audit-logs.export': '/audit-logs/export',
        'settings.index': '/settings',
        'settings.create': '/settings/create',
        'settings.store': '/settings',
        'settings.show': (id) => `/settings/${id}`,
        'settings.edit': (id) => `/settings/${id}/edit`,
        'settings.update': (id) => `/settings/${id}`,
        'settings.destroy': (id) => `/settings/${id}`,
        'settings.update-multiple': '/settings/update-multiple',
        'settings.clear-cache': '/settings/clear-cache',
        'password.request': '/forgot-password',
    };

    const route = routes[name];
    if (typeof route === 'function') {
        return route(params);
    }
    return route || '/';
};

const appName = import.meta.env.VITE_APP_NAME || 'Modular Admin Dashboard';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.jsx`, import.meta.glob('./Pages/**/*.jsx')),
    setup({ el, App, props }) {
        const root = createRoot(el);

        root.render(
            <MockI18nextProvider>
                <App {...props} />
            </MockI18nextProvider>
        );
    },
    progress: {
        color: '#3b82f6',
        showSpinner: true,
    },
    // Ensure proper SPA navigation
    id: 'app',
});
