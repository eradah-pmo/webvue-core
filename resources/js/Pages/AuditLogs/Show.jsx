import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import DashboardLayout from '@/Layouts/DashboardLayout';
import {
    ArrowLeftIcon,
    UserIcon,
    ComputerDesktopIcon,
    GlobeAltIcon,
    ClockIcon,
    ExclamationTriangleIcon,
    ShieldCheckIcon,
    InformationCircleIcon,
    TagIcon,
    DocumentTextIcon,
} from '@heroicons/react/24/outline';

export default function Show({ auditLog }) {
    const { t } = useTranslation();

    const getSeverityIcon = (severity) => {
        switch (severity) {
            case 'critical':
                return <ExclamationTriangleIcon className="w-5 h-5 text-red-500" />;
            case 'warning':
                return <ExclamationTriangleIcon className="w-5 h-5 text-yellow-500" />;
            case 'info':
            default:
                return <InformationCircleIcon className="w-5 h-5 text-blue-500" />;
        }
    };

    const getSeverityBadge = (severity) => {
        const classes = {
            info: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
            warning: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            critical: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        };

        return (
            <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${classes[severity] || classes.info}`}>
                {getSeverityIcon(severity)}
                <span className="ml-1">{t(`audit.severity.${severity}`)}</span>
            </span>
        );
    };

    const formatJson = (data) => {
        if (!data) return null;
        return JSON.stringify(data, null, 2);
    };

    const InfoCard = ({ title, children, icon: Icon }) => (
        <div className="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div className="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div className="flex items-center">
                    {Icon && <Icon className="w-5 h-5 text-gray-400 mr-2" />}
                    <h3 className="text-lg font-medium text-gray-900 dark:text-white">
                        {title}
                    </h3>
                </div>
            </div>
            <div className="px-6 py-4">
                {children}
            </div>
        </div>
    );

    const DataField = ({ label, value, code = false }) => (
        <div className="py-3 sm:py-4 sm:grid sm:grid-cols-3 sm:gap-4">
            <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">
                {label}
            </dt>
            <dd className="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                {code ? (
                    <pre className="bg-gray-100 dark:bg-gray-700 p-3 rounded-md text-xs overflow-x-auto">
                        {value}
                    </pre>
                ) : (
                    value || <span className="text-gray-400">{t('common.not_available')}</span>
                )}
            </dd>
        </div>
    );

    return (
        <DashboardLayout>
            <Head title={`${t('audit.log_details')} #${auditLog.id}`} />

            <div className="py-6">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    {/* Header */}
                    <div className="mb-6">
                        <div className="flex items-center mb-4">
                            <Link
                                href={route('audit-logs.index')}
                                className="inline-flex items-center text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300"
                            >
                                <ArrowLeftIcon className="w-4 h-4 mr-1" />
                                {t('audit.back_to_logs')}
                            </Link>
                        </div>
                        <div className="md:flex md:items-center md:justify-between">
                            <div className="min-w-0 flex-1">
                                <h2 className="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:truncate sm:text-3xl sm:tracking-tight">
                                    {t('audit.log_details')} #{auditLog.id}
                                </h2>
                                <div className="mt-2 flex items-center">
                                    {getSeverityBadge(auditLog.severity)}
                                    <span className="ml-3 text-sm text-gray-500 dark:text-gray-400">
                                        {new Date(auditLog.created_at).toLocaleString()}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {/* Event Information */}
                        <InfoCard title={t('audit.event_information')} icon={DocumentTextIcon}>
                            <dl className="divide-y divide-gray-200 dark:divide-gray-700">
                                <DataField 
                                    label={t('audit.fields.event')} 
                                    value={t(`audit.events.${auditLog.event}`, auditLog.event)} 
                                />
                                <DataField 
                                    label={t('audit.fields.module')} 
                                    value={auditLog.module ? t(`modules.${auditLog.module}`, auditLog.module) : null} 
                                />
                                <DataField 
                                    label={t('audit.fields.action')} 
                                    value={auditLog.action} 
                                />
                                <DataField 
                                    label={t('audit.fields.description')} 
                                    value={auditLog.description} 
                                />
                                <DataField 
                                    label={t('audit.fields.severity')} 
                                    value={getSeverityBadge(auditLog.severity)} 
                                />
                            </dl>
                        </InfoCard>

                        {/* User Information */}
                        <InfoCard title={t('audit.user_information')} icon={UserIcon}>
                            <dl className="divide-y divide-gray-200 dark:divide-gray-700">
                                <DataField 
                                    label={t('audit.fields.user')} 
                                    value={auditLog.user ? (
                                        <div>
                                            <div className="font-medium">{auditLog.user.name}</div>
                                            <div className="text-gray-500 dark:text-gray-400">{auditLog.user.email}</div>
                                        </div>
                                    ) : (
                                        auditLog.metadata?.user_name || t('common.system')
                                    )} 
                                />
                                <DataField 
                                    label={t('audit.fields.user_email')} 
                                    value={auditLog.user?.email || auditLog.metadata?.user_email} 
                                />
                                <DataField 
                                    label={t('audit.fields.session_id')} 
                                    value={auditLog.metadata?.session_id} 
                                />
                            </dl>
                        </InfoCard>

                        {/* Request Information */}
                        <InfoCard title={t('audit.request_information')} icon={GlobeAltIcon}>
                            <dl className="divide-y divide-gray-200 dark:divide-gray-700">
                                <DataField 
                                    label={t('audit.fields.ip_address')} 
                                    value={auditLog.ip_address} 
                                />
                                <DataField 
                                    label={t('audit.fields.url')} 
                                    value={auditLog.metadata?.url} 
                                />
                                <DataField 
                                    label={t('audit.fields.method')} 
                                    value={auditLog.metadata?.method} 
                                />
                                <DataField 
                                    label={t('audit.fields.user_agent')} 
                                    value={auditLog.user_agent} 
                                />
                            </dl>
                        </InfoCard>

                        {/* Model Information */}
                        <InfoCard title={t('audit.model_information')} icon={ComputerDesktopIcon}>
                            <dl className="divide-y divide-gray-200 dark:divide-gray-700">
                                <DataField 
                                    label={t('audit.fields.auditable_type')} 
                                    value={auditLog.auditable_type} 
                                />
                                <DataField 
                                    label={t('audit.fields.auditable_id')} 
                                    value={auditLog.auditable_id} 
                                />
                                {auditLog.auditable && (
                                    <DataField 
                                        label={t('audit.fields.auditable_data')} 
                                        value={
                                            <div>
                                                <div className="font-medium">
                                                    {auditLog.auditable.name || auditLog.auditable.title || `ID: ${auditLog.auditable.id}`}
                                                </div>
                                                {auditLog.auditable.email && (
                                                    <div className="text-gray-500 dark:text-gray-400">
                                                        {auditLog.auditable.email}
                                                    </div>
                                                )}
                                            </div>
                                        } 
                                    />
                                )}
                            </dl>
                        </InfoCard>

                        {/* Tags */}
                        {auditLog.tags && auditLog.tags.length > 0 && (
                            <InfoCard title={t('audit.tags')} icon={TagIcon}>
                                <div className="flex flex-wrap gap-2">
                                    {auditLog.tags.map((tag, index) => (
                                        <span
                                            key={index}
                                            className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300"
                                        >
                                            {tag}
                                        </span>
                                    ))}
                                </div>
                            </InfoCard>
                        )}

                        {/* Timestamps */}
                        <InfoCard title={t('audit.timestamps')} icon={ClockIcon}>
                            <dl className="divide-y divide-gray-200 dark:divide-gray-700">
                                <DataField 
                                    label={t('audit.fields.occurred_at')} 
                                    value={auditLog.metadata?.occurred_at ? new Date(auditLog.metadata.occurred_at).toLocaleString() : null} 
                                />
                                <DataField 
                                    label={t('audit.fields.created_at')} 
                                    value={new Date(auditLog.created_at).toLocaleString()} 
                                />
                                <DataField 
                                    label={t('audit.fields.updated_at')} 
                                    value={new Date(auditLog.updated_at).toLocaleString()} 
                                />
                            </dl>
                        </InfoCard>
                    </div>

                    {/* Data Changes */}
                    {(auditLog.old_values || auditLog.new_values) && (
                        <div className="mt-6">
                            <div className="bg-white dark:bg-gray-800 shadow rounded-lg">
                                <div className="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                                    <h3 className="text-lg font-medium text-gray-900 dark:text-white">
                                        {t('audit.data_changes')}
                                    </h3>
                                </div>
                                <div className="p-6">
                                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                        {auditLog.old_values && (
                                            <div>
                                                <h4 className="text-sm font-medium text-gray-900 dark:text-white mb-3">
                                                    {t('audit.old_values')}
                                                </h4>
                                                <pre className="bg-gray-100 dark:bg-gray-700 p-4 rounded-md text-xs overflow-x-auto">
                                                    {formatJson(auditLog.old_values)}
                                                </pre>
                                            </div>
                                        )}
                                        {auditLog.new_values && (
                                            <div>
                                                <h4 className="text-sm font-medium text-gray-900 dark:text-white mb-3">
                                                    {t('audit.new_values')}
                                                </h4>
                                                <pre className="bg-gray-100 dark:bg-gray-700 p-4 rounded-md text-xs overflow-x-auto">
                                                    {formatJson(auditLog.new_values)}
                                                </pre>
                                            </div>
                                        )}
                                    </div>
                                    {auditLog.metadata?.changed_fields && (
                                        <div className="mt-4">
                                            <h4 className="text-sm font-medium text-gray-900 dark:text-white mb-3">
                                                {t('audit.changed_fields')}
                                            </h4>
                                            <div className="flex flex-wrap gap-2">
                                                {auditLog.metadata.changed_fields.map((field, index) => (
                                                    <span
                                                        key={index}
                                                        className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300"
                                                    >
                                                        {field}
                                                    </span>
                                                ))}
                                            </div>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Additional Metadata */}
                    {auditLog.metadata && Object.keys(auditLog.metadata).length > 0 && (
                        <div className="mt-6">
                            <div className="bg-white dark:bg-gray-800 shadow rounded-lg">
                                <div className="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                                    <h3 className="text-lg font-medium text-gray-900 dark:text-white">
                                        {t('audit.additional_metadata')}
                                    </h3>
                                </div>
                                <div className="p-6">
                                    <pre className="bg-gray-100 dark:bg-gray-700 p-4 rounded-md text-xs overflow-x-auto">
                                        {formatJson(auditLog.metadata)}
                                    </pre>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </DashboardLayout>
    );
}
