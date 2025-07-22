import React, { useState, useEffect } from 'react';
import { useTranslation } from 'react-i18next';
import {
    ExclamationTriangleIcon,
    InformationCircleIcon,
    CheckCircleIcon,
    XCircleIcon,
    XMarkIcon,
    ClockIcon,
    BellIcon,
    ShieldExclamationIcon,
    CpuChipIcon,
    ServerIcon,
} from '@heroicons/react/24/outline';

const alertIcons = {
    warning: ExclamationTriangleIcon,
    info: InformationCircleIcon,
    success: CheckCircleIcon,
    error: XCircleIcon,
    security: ShieldExclamationIcon,
    system: CpuChipIcon,
    server: ServerIcon,
    notification: BellIcon,
};

const AlertItem = ({ alert, onDismiss, index }) => {
    const { t } = useTranslation();
    const [isVisible, setIsVisible] = useState(false);
    const [isRemoving, setIsRemoving] = useState(false);
    
    const Icon = alertIcons[alert.type] || InformationCircleIcon;

    useEffect(() => {
        const timer = setTimeout(() => setIsVisible(true), index * 150);
        return () => clearTimeout(timer);
    }, [index]);

    const getAlertStyles = (type) => {
        const styles = {
            warning: {
                container: 'bg-gradient-to-r from-yellow-50 to-yellow-100 border-yellow-300 dark:from-yellow-900/20 dark:to-yellow-800/20 dark:border-yellow-700',
                text: 'text-yellow-900 dark:text-yellow-200',
                icon: 'text-yellow-600 dark:text-yellow-400',
                accent: 'border-l-yellow-500',
            },
            info: {
                container: 'bg-gradient-to-r from-blue-50 to-blue-100 border-blue-300 dark:from-blue-900/20 dark:to-blue-800/20 dark:border-blue-700',
                text: 'text-blue-900 dark:text-blue-200',
                icon: 'text-blue-600 dark:text-blue-400',
                accent: 'border-l-blue-500',
            },
            success: {
                container: 'bg-gradient-to-r from-green-50 to-green-100 border-green-300 dark:from-green-900/20 dark:to-green-800/20 dark:border-green-700',
                text: 'text-green-900 dark:text-green-200',
                icon: 'text-green-600 dark:text-green-400',
                accent: 'border-l-green-500',
            },
            error: {
                container: 'bg-gradient-to-r from-red-50 to-red-100 border-red-300 dark:from-red-900/20 dark:to-red-800/20 dark:border-red-700',
                text: 'text-red-900 dark:text-red-200',
                icon: 'text-red-600 dark:text-red-400',
                accent: 'border-l-red-500',
            },
            security: {
                container: 'bg-gradient-to-r from-purple-50 to-purple-100 border-purple-300 dark:from-purple-900/20 dark:to-purple-800/20 dark:border-purple-700',
                text: 'text-purple-900 dark:text-purple-200',
                icon: 'text-purple-600 dark:text-purple-400',
                accent: 'border-l-purple-500',
            },
            system: {
                container: 'bg-gradient-to-r from-indigo-50 to-indigo-100 border-indigo-300 dark:from-indigo-900/20 dark:to-indigo-800/20 dark:border-indigo-700',
                text: 'text-indigo-900 dark:text-indigo-200',
                icon: 'text-indigo-600 dark:text-indigo-400',
                accent: 'border-l-indigo-500',
            },
        };
        return styles[type] || styles.info;
    };

    const handleDismiss = () => {
        setIsRemoving(true);
        setTimeout(() => onDismiss(alert.id), 300);
    };

    const formatTimestamp = (timestamp) => {
        if (!timestamp) return '';
        const date = new Date(timestamp);
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    };

    const styles = getAlertStyles(alert.type);

    return (
        <div 
            className={`
                border-l-4 rounded-lg border shadow-sm transition-all duration-500 transform
                ${styles.container} ${styles.accent}
                ${isVisible && !isRemoving ? 'translate-x-0 opacity-100 scale-100' : 'translate-x-4 opacity-0 scale-95'}
                ${isRemoving ? 'translate-x-full opacity-0 scale-95' : ''}
                hover:shadow-md hover:scale-[1.02] group
            `}
        >
            <div className="p-4">
                <div className="flex items-start space-x-3">
                    <div className={`
                        w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0
                        bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm
                        group-hover:scale-110 transition-transform duration-200
                    `}>
                        <Icon className={`w-5 h-5 ${styles.icon}`} />
                    </div>
                    
                    <div className="flex-1 min-w-0">
                        <div className="flex items-center justify-between mb-1">
                            <h4 className={`font-semibold text-sm ${styles.text}`}>
                                {alert.title}
                            </h4>
                            
                            <div className="flex items-center space-x-2">
                                {alert.timestamp && (
                                    <div className="flex items-center space-x-1 text-xs opacity-75">
                                        <ClockIcon className="w-3 h-3" />
                                        <span>{formatTimestamp(alert.timestamp)}</span>
                                    </div>
                                )}
                                
                                {alert.priority && (
                                    <span className={`
                                        inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                        ${alert.priority === 'high' 
                                            ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'
                                            : alert.priority === 'medium'
                                            ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400'
                                            : 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400'
                                        }
                                    `}>
                                        {t(`dashboard:alerts.priority.${alert.priority}`)}
                                    </span>
                                )}
                            </div>
                        </div>
                        
                        <p className={`text-sm ${styles.text} opacity-90 leading-relaxed`}>
                            {alert.message}
                        </p>
                        
                        {alert.details && (
                            <div className="mt-2 p-2 bg-white/30 dark:bg-gray-800/30 rounded text-xs opacity-80">
                                {alert.details}
                            </div>
                        )}
                        
                        {alert.actions && alert.actions.length > 0 && (
                            <div className="mt-3 flex flex-wrap gap-2">
                                {alert.actions.map((action, actionIndex) => (
                                    <button
                                        key={actionIndex}
                                        onClick={action.onClick}
                                        className={`
                                            px-3 py-1 text-xs font-medium rounded-full transition-all duration-200
                                            ${action.primary 
                                                ? `${styles.icon} bg-white/80 dark:bg-gray-800/80 hover:bg-white dark:hover:bg-gray-700`
                                                : 'text-gray-600 dark:text-gray-400 bg-white/50 dark:bg-gray-800/50 hover:bg-white/80 dark:hover:bg-gray-700/80'
                                            }
                                        `}
                                    >
                                        {action.label}
                                    </button>
                                ))}
                            </div>
                        )}
                    </div>
                    
                    {alert.dismissible !== false && (
                        <button
                            onClick={handleDismiss}
                            className={`
                                flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center
                                text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300
                                hover:bg-white/50 dark:hover:bg-gray-800/50 transition-all duration-200
                                group-hover:scale-110
                            `}
                        >
                            <XMarkIcon className="w-4 h-4" />
                        </button>
                    )}
                </div>
            </div>
        </div>
    );
};

export default function SystemAlerts({ 
    alerts = [], 
    onDismiss, 
    title, 
    maxVisible = 5, 
    loading = false 
}) {
    const { t } = useTranslation();
    const [dismissedAlerts, setDismissedAlerts] = useState(new Set());
    const [showAll, setShowAll] = useState(false);

    const handleDismiss = (alertId) => {
        setDismissedAlerts(prev => new Set([...prev, alertId]));
        if (onDismiss) {
            onDismiss(alertId);
        }
    };

    const visibleAlerts = alerts.filter(alert => !dismissedAlerts.has(alert.id));
    const displayedAlerts = showAll ? visibleAlerts : visibleAlerts.slice(0, maxVisible);
    const hasMore = visibleAlerts.length > maxVisible;

    if (loading) {
        return (
            <div className="space-y-4">
                <div className="h-6 bg-gray-200 dark:bg-gray-700 rounded w-1/4 animate-pulse" />
                {[...Array(3)].map((_, index) => (
                    <div key={index} className="border-l-4 border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 p-4 animate-pulse">
                        <div className="flex items-start space-x-3">
                            <div className="w-10 h-10 bg-gray-200 dark:bg-gray-700 rounded-full" />
                            <div className="flex-1 space-y-2">
                                <div className="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4" />
                                <div className="h-3 bg-gray-200 dark:bg-gray-700 rounded w-full" />
                            </div>
                        </div>
                    </div>
                ))}
            </div>
        );
    }

    if (visibleAlerts.length === 0) {
        return (
            <div className="text-center py-8">
                <div className="w-16 h-16 mx-auto mb-4 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                    <CheckCircleIcon className="w-8 h-8 text-green-600 dark:text-green-400" />
                </div>
                <p className="text-gray-600 dark:text-gray-400 font-medium">
                    {t('dashboard:alerts.no_alerts')}
                </p>
                <p className="text-sm text-gray-500 dark:text-gray-500 mt-1">
                    {t('dashboard:alerts.all_clear')}
                </p>
            </div>
        );
    }

    return (
        <div className="space-y-4">
            {title && (
                <div className="flex items-center justify-between">
                    <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                        {title}
                    </h3>
                    <span className="text-sm text-gray-500 dark:text-gray-400">
                        {visibleAlerts.length} {t('dashboard:alerts.active')}
                    </span>
                </div>
            )}
            
            <div className="space-y-3">
                {displayedAlerts.map((alert, index) => (
                    <AlertItem 
                        key={alert.id} 
                        alert={alert} 
                        onDismiss={handleDismiss} 
                        index={index}
                    />
                ))}
            </div>
            
            {hasMore && (
                <div className="text-center pt-2">
                    <button
                        onClick={() => setShowAll(!showAll)}
                        className="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium transition-colors duration-200"
                    >
                        {showAll 
                            ? t('dashboard:alerts.show_less')
                            : t('dashboard:alerts.show_more', { count: visibleAlerts.length - maxVisible })
                        }
                    </button>
                </div>
            )}
        </div>
    );
}
