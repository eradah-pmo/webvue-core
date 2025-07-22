import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';

// Mock translations
const resources = {
  en: {
    translation: {
      'dashboard.total_users': 'Total Users',
      'dashboard.active_sessions': 'Active Sessions',
      'dashboard.revenue': 'Revenue',
      'dashboard.no_data': 'No data available',
      'dashboard.loading': 'Loading...',
      'common.save': 'Save',
      'common.cancel': 'Cancel',
      'common.delete': 'Delete',
      'common.edit': 'Edit',
      'common.view': 'View',
      'common.search': 'Search',
      'common.filter': 'Filter',
      'common.export': 'Export',
      'alerts.success': 'Success',
      'alerts.error': 'Error',
      'alerts.warning': 'Warning',
      'alerts.info': 'Information',
      'stats.increase': 'Increase',
      'stats.decrease': 'Decrease',
      'stats.stable': 'Stable'
    }
  },
  ar: {
    translation: {
      'dashboard.total_users': 'إجمالي المستخدمين',
      'dashboard.active_sessions': 'الجلسات النشطة',
      'dashboard.revenue': 'الإيرادات',
      'dashboard.no_data': 'لا توجد بيانات متاحة',
      'dashboard.loading': 'جاري التحميل...',
      'common.save': 'حفظ',
      'common.cancel': 'إلغاء',
      'common.delete': 'حذف',
      'common.edit': 'تعديل',
      'common.view': 'عرض',
      'common.search': 'بحث',
      'common.filter': 'تصفية',
      'common.export': 'تصدير',
      'alerts.success': 'نجح',
      'alerts.error': 'خطأ',
      'alerts.warning': 'تحذير',
      'alerts.info': 'معلومات',
      'stats.increase': 'زيادة',
      'stats.decrease': 'نقصان',
      'stats.stable': 'مستقر'
    }
  }
};

i18n
  .use(initReactI18next)
  .init({
    resources,
    lng: 'en',
    fallbackLng: 'en',
    debug: false,
    interpolation: {
      escapeValue: false
    },
    react: {
      useSuspense: false
    }
  });

export default i18n;
