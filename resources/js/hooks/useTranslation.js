// Global mock useTranslation hook for English-only mode
// This replaces react-i18next's useTranslation during development

import { mockUseTranslation } from '../i18n/mock';

// Export the mock hook as the default useTranslation
export const useTranslation = mockUseTranslation;

// Also export as named export for compatibility
export { useTranslation as default };

console.log('🔧 Global mock useTranslation hook loaded');
