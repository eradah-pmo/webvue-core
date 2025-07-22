import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';

// Simple inline resources for testing
const resources = {
    en: {
        common: {
            "appName": "Modular Admin Dashboard",
            "welcome": "Welcome",
            "welcomeMessage": "A comprehensive modular administration system with advanced security, RBAC, and audit logging capabilities.",
            "login": "Login",
            "register": "Register",
            "dashboard": "Dashboard",
            "features": {
                "rbac": "Role-Based Access",
                "security": "Advanced Security",
                "analytics": "Real-time Analytics",
                "audit": "Audit Logging"
            },
            "featureDescriptions": {
                "rbac": "Horizontal and vertical role-based access control system.",
                "security": "Enterprise-grade security with comprehensive protection.",
                "analytics": "Detailed analytics and reporting capabilities.",
                "audit": "Complete audit trail of all system activities."
            }
        }
    },
    ar: {
        common: {
            "appName": "لوحة تحكم معيارية",
            "welcome": "مرحباً بك",
            "welcomeMessage": "نظام إدارة معياري شامل مع ميزات أمان متقدمة، وتحكم بالصلاحيات، وتسجيل للتدقيق.",
            "login": "تسجيل الدخول",
            "register": "إنشاء حساب",
            "dashboard": "لوحة التحكم",
            "features": {
                "rbac": "نظام صلاحيات متقدم",
                "security": "أمان متطور",
                "analytics": "تحليلات مباشرة",
                "audit": "سجلات التدقيق"
            },
            "featureDescriptions": {
                "rbac": "نظام صلاحيات أفقي وعمودي متكامل.",
                "security": "حماية على مستوى المؤسسات مع حماية شاملة.",
                "analytics": "قدرات تحليل وتقارير مفصلة.",
                "audit": "سجل تدقيق كامل لجميع أنشطة النظام."
            }
        }
    }
};

i18n
    .use(initReactI18next)
    .init({
        resources,
        lng: 'en', // default language
        fallbackLng: 'en',
        defaultNS: 'common',
        
        interpolation: {
            escapeValue: false,
        },

        react: {
            useSuspense: false,
        },
        
        debug: true, // Enable debug mode
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
