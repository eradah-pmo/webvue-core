import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';
import LanguageDetector from 'i18next-browser-languagedetector';
import Backend from 'i18next-http-backend';

// Import translation files (JS instead of JSON for better Vite compatibility)
import enCommon from './locales/en/common.js';
import enAuth from './locales/en/auth.js';
import enDashboard from './locales/en/dashboard.js';
import enUsers from './locales/en/users.js';
import enRoles from './locales/en/roles.js';
import enDepartments from './locales/en/departments.js';

import arCommon from './locales/ar/common.js';
import arAuth from './locales/ar/auth.js';
import arDashboard from './locales/ar/dashboard.js';
import arUsers from './locales/ar/users.js';
import arRoles from './locales/ar/roles.js';
import arDepartments from './locales/ar/departments.js';

const resources = {
    en: {
        common: enCommon,
        auth: enAuth,
        dashboard: enDashboard,
        users: enUsers,
        roles: enRoles,
        departments: enDepartments,
    },
    ar: {
        common: arCommon,
        auth: arAuth,
        dashboard: arDashboard,
        users: arUsers,
        roles: arRoles,
        departments: arDepartments,
    },
};

i18n
    .use(Backend)
    .use(LanguageDetector)
    .use(initReactI18next)
    .init({
        resources,
        fallbackLng: 'en',
        defaultNS: 'common',
        
        detection: {
            order: ['localStorage', 'navigator', 'htmlTag'],
            caches: ['localStorage'],
        },

        interpolation: {
            escapeValue: false,
        },

        react: {
            useSuspense: false,
        },
    });

// Handle RTL direction change
i18n.on('languageChanged', (lng) => {
    const isRTL = lng === 'ar';
    document.documentElement.dir = isRTL ? 'rtl' : 'ltr';
    document.documentElement.lang = lng;
    
    // Update body class for RTL styling
    if (isRTL) {
        document.body.classList.add('rtl');
        document.body.classList.remove('ltr');
    } else {
        document.body.classList.add('ltr');
        document.body.classList.remove('rtl');
    }
});

export default i18n;
