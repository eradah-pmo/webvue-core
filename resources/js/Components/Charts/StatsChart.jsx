import React, { useState, useEffect } from 'react';
import {
    LineChart,
    Line,
    AreaChart,
    Area,
    BarChart,
    Bar,
    XAxis,
    YAxis,
    CartesianGrid,
    Tooltip,
    ResponsiveContainer,
    PieChart,
    Pie,
    Cell,
    Legend,
} from 'recharts';
import { useTranslation } from 'react-i18next';
import {
    ChartBarIcon,
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon,
    EyeIcon,
    EyeSlashIcon,
} from '@heroicons/react/24/outline';

const COLORS = {
    primary: ['#3B82F6', '#1D4ED8', '#1E40AF'],
    success: ['#10B981', '#059669', '#047857'],
    warning: ['#F59E0B', '#D97706', '#B45309'],
    danger: ['#EF4444', '#DC2626', '#B91C1C'],
    purple: ['#8B5CF6', '#7C3AED', '#6D28D9'],
    cyan: ['#06B6D4', '#0891B2', '#0E7490'],
};

const CustomTooltip = ({ active, payload, label, isDark }) => {
    if (active && payload && payload.length) {
        return (
            <div className={`
                p-3 rounded-lg shadow-lg border backdrop-blur-sm
                ${isDark 
                    ? 'bg-gray-800/90 border-gray-600 text-white' 
                    : 'bg-white/90 border-gray-200 text-gray-900'
                }
            `}>
                <p className="font-medium mb-2">{label}</p>
                {payload.map((entry, index) => (
                    <p key={index} className="text-sm" style={{ color: entry.color }}>
                        {`${entry.name}: ${entry.value}`}
                    </p>
                ))}
            </div>
        );
    }
    return null;
};

export function LineStatsChart({ 
    data = [], 
    dataKey, 
    title, 
    color = COLORS.primary[0],
    showGrid = true,
    showDots = true,
    animated = true,
    height = 300
}) {
    const { t } = useTranslation();
    const [isVisible, setIsVisible] = useState(false);
    const [isDark, setIsDark] = useState(false);

    useEffect(() => {
        setIsVisible(true);
        setIsDark(document.documentElement.classList.contains('dark'));
    }, []);

    const getTrendIcon = () => {
        if (data.length < 2) return null;
        const lastValue = data[data.length - 1][dataKey];
        const prevValue = data[data.length - 2][dataKey];
        
        if (lastValue > prevValue) {
            return <ArrowTrendingUpIcon className="w-4 h-4 text-green-500" />;
        } else if (lastValue < prevValue) {
            return <ArrowTrendingDownIcon className="w-4 h-4 text-red-500" />;
        }
        return null;
    };

    return (
        <div className={`
            bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700
            transition-all duration-500 transform
            ${isVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0'}
            hover:shadow-xl hover:scale-[1.02]
        `}>
            <div className="p-6 border-b border-gray-200 dark:border-gray-700">
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-3">
                        <div className="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                            <ChartBarIcon className="w-5 h-5 text-white" />
                        </div>
                        <div>
                            <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                                {title}
                            </h3>
                            <p className="text-sm text-gray-500 dark:text-gray-400">
                                {data.length} {t('dashboard:charts.data_points')}
                            </p>
                        </div>
                    </div>
                    <div className="flex items-center space-x-2">
                        {getTrendIcon()}
                    </div>
                </div>
            </div>
            
            <div className="p-6">
                <ResponsiveContainer width="100%" height={height}>
                    <LineChart data={data} margin={{ top: 5, right: 30, left: 20, bottom: 5 }}>
                        {showGrid && (
                            <CartesianGrid 
                                strokeDasharray="3 3" 
                                stroke={isDark ? '#374151' : '#E5E7EB'}
                                opacity={0.5}
                            />
                        )}
                        <XAxis 
                            dataKey="name" 
                            tick={{ fontSize: 12, fill: isDark ? '#9CA3AF' : '#6B7280' }}
                            axisLine={{ stroke: isDark ? '#4B5563' : '#D1D5DB' }}
                            tickLine={{ stroke: isDark ? '#4B5563' : '#D1D5DB' }}
                        />
                        <YAxis 
                            tick={{ fontSize: 12, fill: isDark ? '#9CA3AF' : '#6B7280' }}
                            axisLine={{ stroke: isDark ? '#4B5563' : '#D1D5DB' }}
                            tickLine={{ stroke: isDark ? '#4B5563' : '#D1D5DB' }}
                        />
                        <Tooltip content={<CustomTooltip isDark={isDark} />} />
                        <Line 
                            type="monotone" 
                            dataKey={dataKey} 
                            stroke={color}
                            strokeWidth={3}
                            dot={showDots ? { r: 4, fill: color } : false}
                            activeDot={{ r: 6, fill: color, stroke: '#fff', strokeWidth: 2 }}
                            animationDuration={animated ? 1500 : 0}
                            animationEasing="ease-in-out"
                        />
                    </LineChart>
                </ResponsiveContainer>
            </div>
        </div>
    );
}

export function AreaStatsChart({ 
    data = [], 
    dataKey, 
    title, 
    color = COLORS.primary[0],
    showGrid = true,
    animated = true,
    height = 300
}) {
    const { t } = useTranslation();
    const [isVisible, setIsVisible] = useState(false);
    const [isDark, setIsDark] = useState(false);

    useEffect(() => {
        setIsVisible(true);
        setIsDark(document.documentElement.classList.contains('dark'));
    }, []);

    return (
        <div className={`
            bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700
            transition-all duration-500 transform
            ${isVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0'}
            hover:shadow-xl hover:scale-[1.02]
        `}>
            <div className="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                    {title}
                </h3>
            </div>
            
            <div className="p-6">
                <ResponsiveContainer width="100%" height={height}>
                    <AreaChart data={data} margin={{ top: 5, right: 30, left: 20, bottom: 5 }}>
                        {showGrid && (
                            <CartesianGrid 
                                strokeDasharray="3 3" 
                                stroke={isDark ? '#374151' : '#E5E7EB'}
                                opacity={0.5}
                            />
                        )}
                        <XAxis 
                            dataKey="name" 
                            tick={{ fontSize: 12, fill: isDark ? '#9CA3AF' : '#6B7280' }}
                        />
                        <YAxis 
                            tick={{ fontSize: 12, fill: isDark ? '#9CA3AF' : '#6B7280' }}
                        />
                        <Tooltip content={<CustomTooltip isDark={isDark} />} />
                        <Area 
                            type="monotone" 
                            dataKey={dataKey} 
                            stroke={color}
                            fill={`${color}20`}
                            strokeWidth={2}
                            animationDuration={animated ? 1500 : 0}
                        />
                    </AreaChart>
                </ResponsiveContainer>
            </div>
        </div>
    );
}

export function BarStatsChart({ 
    data = [], 
    dataKey, 
    title, 
    color = COLORS.primary[0],
    showGrid = true,
    animated = true,
    height = 300
}) {
    const { t } = useTranslation();
    const [isVisible, setIsVisible] = useState(false);
    const [isDark, setIsDark] = useState(false);

    useEffect(() => {
        setIsVisible(true);
        setIsDark(document.documentElement.classList.contains('dark'));
    }, []);

    return (
        <div className={`
            bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700
            transition-all duration-500 transform
            ${isVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0'}
            hover:shadow-xl hover:scale-[1.02]
        `}>
            <div className="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                    {title}
                </h3>
            </div>
            
            <div className="p-6">
                <ResponsiveContainer width="100%" height={height}>
                    <BarChart data={data} margin={{ top: 5, right: 30, left: 20, bottom: 5 }}>
                        {showGrid && (
                            <CartesianGrid 
                                strokeDasharray="3 3" 
                                stroke={isDark ? '#374151' : '#E5E7EB'}
                                opacity={0.5}
                            />
                        )}
                        <XAxis 
                            dataKey="name" 
                            tick={{ fontSize: 12, fill: isDark ? '#9CA3AF' : '#6B7280' }}
                        />
                        <YAxis 
                            tick={{ fontSize: 12, fill: isDark ? '#9CA3AF' : '#6B7280' }}
                        />
                        <Tooltip content={<CustomTooltip isDark={isDark} />} />
                        <Bar 
                            dataKey={dataKey} 
                            fill={color}
                            radius={[4, 4, 0, 0]}
                            animationDuration={animated ? 1000 : 0}
                        />
                    </BarChart>
                </ResponsiveContainer>
            </div>
        </div>
    );
}

export function PieStatsChart({ 
    data = [], 
    title, 
    colors = COLORS.primary,
    showLegend = true,
    animated = true,
    height = 300
}) {
    const { t } = useTranslation();
    const [isVisible, setIsVisible] = useState(false);
    const [isDark, setIsDark] = useState(false);

    useEffect(() => {
        setIsVisible(true);
        setIsDark(document.documentElement.classList.contains('dark'));
    }, []);

    return (
        <div className={`
            bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700
            transition-all duration-500 transform
            ${isVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0'}
            hover:shadow-xl hover:scale-[1.02]
        `}>
            <div className="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                    {title}
                </h3>
            </div>
            
            <div className="p-6">
                <ResponsiveContainer width="100%" height={height}>
                    <PieChart>
                        <Pie
                            data={data}
                            cx="50%"
                            cy="50%"
                            outerRadius={80}
                            fill="#8884d8"
                            dataKey="value"
                            animationDuration={animated ? 1000 : 0}
                        >
                            {data.map((entry, index) => (
                                <Cell 
                                    key={`cell-${index}`} 
                                    fill={colors[index % colors.length]} 
                                />
                            ))}
                        </Pie>
                        <Tooltip content={<CustomTooltip isDark={isDark} />} />
                        {showLegend && <Legend />}
                    </PieChart>
                </ResponsiveContainer>
            </div>
        </div>
    );
}

export default {
    LineStatsChart,
    AreaStatsChart,
    BarStatsChart,
    PieStatsChart,
    COLORS,
};
