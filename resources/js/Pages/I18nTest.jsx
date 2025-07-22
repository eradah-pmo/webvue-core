import React from 'react';
import { Head } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

export default function I18nTest() {
  const { t, i18n } = useTranslation(['common']);

  // Debug information
  const debugInfo = {
    currentLanguage: i18n.language,
    isInitialized: i18n.isInitialized,
    hasResourceBundle: i18n.hasResourceBundle('en', 'common'),
    hasResourceBundleAr: i18n.hasResourceBundle('ar', 'common'),
    resources: i18n.options.resources,
    store: i18n.store
  };

  console.log('I18n Debug Info:', debugInfo);

  // Test different translation methods
  const testTranslations = {
    direct: t('appName'),
    withNamespace: t('common:appName'),
    nested: t('features.rbac'),
    nestedWithNamespace: t('common:features.rbac'),
    fallback: t('nonexistent', 'Fallback Text')
  };

  console.log('Test Translations:', testTranslations);

  return (
    <>
      <Head title="I18n Test" />
      
      <div className="min-h-screen bg-gray-100 py-12">
        <div className="max-w-4xl mx-auto px-4">
          <h1 className="text-3xl font-bold mb-8">I18n Debug Test</h1>
          
          <div className="bg-white rounded-lg shadow p-6 mb-6">
            <h2 className="text-xl font-semibold mb-4">Debug Information</h2>
            <pre className="bg-gray-100 p-4 rounded text-sm overflow-auto">
              {JSON.stringify(debugInfo, null, 2)}
            </pre>
          </div>

          <div className="bg-white rounded-lg shadow p-6 mb-6">
            <h2 className="text-xl font-semibold mb-4">Translation Tests</h2>
            <div className="space-y-2">
              <div><strong>Direct:</strong> {testTranslations.direct}</div>
              <div><strong>With Namespace:</strong> {testTranslations.withNamespace}</div>
              <div><strong>Nested:</strong> {testTranslations.nested}</div>
              <div><strong>Nested with Namespace:</strong> {testTranslations.nestedWithNamespace}</div>
              <div><strong>Fallback:</strong> {testTranslations.fallback}</div>
            </div>
          </div>

          <div className="bg-white rounded-lg shadow p-6">
            <h2 className="text-xl font-semibold mb-4">Language Controls</h2>
            <div className="space-x-4">
              <button 
                onClick={() => i18n.changeLanguage('en')}
                className="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
              >
                Switch to English
              </button>
              <button 
                onClick={() => i18n.changeLanguage('ar')}
                className="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600"
              >
                Switch to Arabic
              </button>
            </div>
            <div className="mt-4">
              <strong>Current Language:</strong> {i18n.language}
            </div>
          </div>
        </div>
      </div>
    </>
  );
}
