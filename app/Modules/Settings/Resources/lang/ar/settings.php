<?php

return [
    // General
    'title' => 'الإعدادات',
    'settings' => 'الإعدادات',
    'setting' => 'إعداد',
    'manage_settings' => 'إدارة الإعدادات',
    'system_settings' => 'إعدادات النظام',
    
    // Actions
    'create_setting' => 'إنشاء إعداد',
    'edit_setting' => 'تعديل الإعداد',
    'delete_setting' => 'حذف الإعداد',
    'save_setting' => 'حفظ الإعداد',
    'update_setting' => 'تحديث الإعداد',
    'clear_cache' => 'مسح التخزين المؤقت',
    'update_multiple' => 'تحديث متعدد',
    
    // Fields
    'key' => 'المفتاح',
    'category' => 'الفئة',
    'value' => 'القيمة',
    'type' => 'النوع',
    'description' => 'الوصف',
    'validation_rules' => 'قواعد التحقق',
    'options' => 'الخيارات',
    'is_public' => 'عام',
    'is_encrypted' => 'مشفر',
    'sort_order' => 'ترتيب العرض',
    'active' => 'نشط',
    
    // Categories
    'general' => 'عام',
    'security' => 'الأمان',
    'mail' => 'البريد الإلكتروني',
    'ui' => 'واجهة المستخدم',
    'files' => 'الملفات',
    'notifications' => 'الإشعارات',
    'backup' => 'النسخ الاحتياطي',
    
    // Types
    'string' => 'نص',
    'number' => 'رقم',
    'boolean' => 'نعم/لا',
    'json' => 'JSON',
    'file' => 'ملف',
    
    // Messages
    'created_successfully' => 'تم إنشاء الإعداد بنجاح',
    'updated_successfully' => 'تم تحديث الإعداد بنجاح',
    'deleted_successfully' => 'تم حذف الإعداد بنجاح',
    'creation_failed' => 'فشل في إنشاء الإعداد',
    'update_failed' => 'فشل في تحديث الإعداد',
    'deletion_failed' => 'فشل في حذف الإعداد',
    'cache_cleared' => 'تم مسح التخزين المؤقت للإعدادات بنجاح',
    'cache_clear_failed' => 'فشل في مسح التخزين المؤقت للإعدادات',
    
    // Validation
    'key_required' => 'مفتاح الإعداد مطلوب',
    'key_unique' => 'مفتاح الإعداد يجب أن يكون فريداً',
    'key_format' => 'مفتاح الإعداد يجب أن يحتوي على أحرف صغيرة وأرقام وشرطة سفلية ونقاط فقط',
    'category_required' => 'الفئة مطلوبة',
    'type_required' => 'النوع مطلوب',
    'type_invalid' => 'نوع الإعداد غير صحيح',
    'file_too_large' => 'حجم الملف يجب ألا يتجاوز 10 ميجابايت',
    
    // Help Text
    'key_help' => 'معرف فريد لهذا الإعداد (مثال: app.name)',
    'category_help' => 'تجميع الإعدادات حسب الفئة لتنظيم أفضل',
    'type_help' => 'نوع البيانات يحدد كيفية تخزين والتحقق من القيمة',
    'public_help' => 'الإعدادات العامة يمكن الوصول إليها من تطبيقات الواجهة الأمامية',
    'encrypted_help' => 'الإعدادات المشفرة يتم تخزينها بشكل آمن في قاعدة البيانات',
    'sort_order_help' => 'الأرقام الأصغر تظهر أولاً في القوائم',
    
    // Placeholders
    'search_settings' => 'البحث في الإعدادات...',
    'select_category' => 'اختر الفئة',
    'select_type' => 'اختر النوع',
    'enter_value' => 'أدخل القيمة',
    'enter_description' => 'أدخل الوصف',
];
