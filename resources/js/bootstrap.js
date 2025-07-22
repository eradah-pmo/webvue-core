import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Enhanced CSRF token handling with better error recovery
function setupCsrfToken() {
    const token = document.head.querySelector('meta[name="csrf-token"]');
    if (token && token.content) {
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
        window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        console.log('✅ CSRF token configured for axios:', token.content.substring(0, 10) + '...');
        return token.content;
    } else {
        console.error('❌ CSRF token not found in meta tags!');
        return null;
    }
}

// Setup CSRF token initially
let csrfToken = setupCsrfToken();

// Enhanced CSRF token refresh with multiple fallbacks
window.refreshCsrfToken = async function() {
    console.log('🔄 Refreshing CSRF token...');
    
    try {
        // Try Sanctum CSRF cookie first
        await fetch('/sanctum/csrf-cookie', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        // Try custom CSRF token endpoint as fallback
        const response = await fetch('/csrf-token', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            if (data.csrf_token) {
                // Update meta tag
                const metaTag = document.head.querySelector('meta[name="csrf-token"]');
                if (metaTag) {
                    metaTag.content = data.csrf_token;
                }
                
                // Re-setup CSRF token
                csrfToken = setupCsrfToken();
                console.log('✅ CSRF token refreshed successfully');
                return csrfToken;
            }
        }
        
        // Fallback: just re-setup existing token
        csrfToken = setupCsrfToken();
        return csrfToken;
        
    } catch (error) {
        console.error('❌ CSRF token refresh failed:', error);
        // Last resort: try to get token from current meta tag
        csrfToken = setupCsrfToken();
        return csrfToken;
    }
};

// Add request interceptor for authentication
window.axios.interceptors.request.use(
    (config) => {
        // Ensure config and headers exist
        if (!config) {
            console.error('❌ Axios config is undefined');
            return {};
        }
        
        if (!config.headers) {
            config.headers = {};
        }
        
        // Add CSRF token if available
        if (csrfToken && !config.headers['X-CSRF-TOKEN']) {
            config.headers['X-CSRF-TOKEN'] = csrfToken;
        }
        
        // Ensure X-Requested-With header
        if (!config.headers['X-Requested-With']) {
            config.headers['X-Requested-With'] = 'XMLHttpRequest';
        }
        
        return config;
    },
    (error) => {
        console.error('❌ Axios request interceptor error:', error);
        return Promise.reject(error);
    }
);

// Enhanced response interceptor with retry logic for 419 errors
window.axios.interceptors.response.use(
    (response) => {
        // Check for fresh CSRF token in response headers
        const newToken = response.headers['x-csrf-token'];
        if (newToken && newToken !== csrfToken) {
            console.log('🔄 Updating CSRF token from response header');
            const metaTag = document.head.querySelector('meta[name="csrf-token"]');
            if (metaTag) {
                metaTag.content = newToken;
            }
            csrfToken = newToken;
            window.axios.defaults.headers.common['X-CSRF-TOKEN'] = newToken;
        }
        return response;
    },
    async (error) => {
        const originalRequest = error.config;
        console.log('🔍 Axios response error:', error);
        
        // Handle different error scenarios
        if (error.response) {
            const status = error.response.status;
            
            if (status === 401) {
                console.log('🔐 Unauthorized - redirecting to login');
                window.location.href = '/login';
                return Promise.reject(error);
            } 
            
            if (status === 419 && !originalRequest._retry) {
                console.log('🔄 CSRF token expired - attempting refresh and retry');
                originalRequest._retry = true;
                
                try {
                    // Refresh CSRF token
                    await window.refreshCsrfToken();
                    
                    // Update the original request with new token
                    if (csrfToken) {
                        originalRequest.headers['X-CSRF-TOKEN'] = csrfToken;
                    }
                    
                    console.log('✅ Retrying request with refreshed CSRF token');
                    return window.axios(originalRequest);
                    
                } catch (refreshError) {
                    console.error('❌ CSRF refresh failed:', refreshError);
                    
                    // If this is a login request, reload the page
                    if (originalRequest.url && originalRequest.url.includes('/login')) {
                        console.log('🔄 Login CSRF failed - reloading page');
                        window.location.reload();
                        return Promise.reject(error);
                    }
                    
                    // For other requests, redirect to login
                    window.location.href = '/login';
                    return Promise.reject(error);
                }
            }
        } else if (error.request) {
            // Request was made but no response received
            console.error('❌ No response received:', error.request);
        } else {
            // Something else happened
            console.error('❌ Axios error:', error.message);
        }
        
        return Promise.reject(error);
    }
);
