import React from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import '@testing-library/jest-dom';
import { I18nextProvider } from 'react-i18next';
import i18n from '../../../__mocks__/i18n';
import AdvancedStats from '../../../../resources/js/Components/Dashboard/AdvancedStats';

// Mock Chart.js
jest.mock('chart.js/auto', () => ({
  Chart: jest.fn().mockImplementation(() => ({
    destroy: jest.fn(),
    update: jest.fn(),
    data: { datasets: [] },
    options: {}
  }))
}));

const mockStats = [
  {
    id: 1,
    title: 'Total Users',
    value: 1250,
    change: 12.5,
    trend: 'up',
    color: 'blue',
    icon: 'UserGroupIcon',
    chartData: [10, 20, 30, 25, 40, 35, 50]
  },
  {
    id: 2,
    title: 'Active Sessions',
    value: 89,
    change: -5.2,
    trend: 'down',
    color: 'green',
    icon: 'ComputerDesktopIcon',
    chartData: [30, 25, 35, 20, 15, 25, 30]
  },
  {
    id: 3,
    title: 'Revenue',
    value: 52400,
    change: 8.1,
    trend: 'up',
    color: 'purple',
    icon: 'CurrencyDollarIcon',
    chartData: [100, 120, 110, 140, 130, 150, 160]
  }
];

const renderWithI18n = (component) => {
  return render(
    <I18nextProvider i18n={i18n}>
      {component}
    </I18nextProvider>
  );
};

describe('AdvancedStats Component', () => {
  beforeEach(() => {
    jest.clearAllMocks();
  });

  test('renders stats cards correctly', () => {
    renderWithI18n(<AdvancedStats stats={mockStats} />);
    
    expect(screen.getByText('Total Users')).toBeInTheDocument();
    expect(screen.getByText('1,250')).toBeInTheDocument();
    expect(screen.getByText('Active Sessions')).toBeInTheDocument();
    expect(screen.getByText('89')).toBeInTheDocument();
    expect(screen.getByText('Revenue')).toBeInTheDocument();
    expect(screen.getByText('$52,400')).toBeInTheDocument();
  });

  test('displays trend indicators correctly', () => {
    renderWithI18n(<AdvancedStats stats={mockStats} />);
    
    const upTrends = screen.getAllByText(/12\.5%|8\.1%/);
    const downTrend = screen.getByText('5.2%');
    
    expect(upTrends).toHaveLength(2);
    expect(downTrend).toBeInTheDocument();
  });

  test('applies correct color themes', () => {
    renderWithI18n(<AdvancedStats stats={mockStats} />);
    
    const cards = screen.getAllByTestId(/stat-card-/);
    expect(cards).toHaveLength(3);
    
    // التحقق من وجود الألوان المناسبة في الكلاسات
    expect(cards[0]).toHaveClass('bg-gradient-to-br');
    expect(cards[1]).toHaveClass('bg-gradient-to-br');
    expect(cards[2]).toHaveClass('bg-gradient-to-br');
  });

  test('handles empty stats gracefully', () => {
    renderWithI18n(<AdvancedStats stats={[]} />);
    
    expect(screen.getByText(/no.*data.*available/i)).toBeInTheDocument();
  });

  test('handles missing chart data', () => {
    const statsWithoutChart = [{
      id: 1,
      title: 'Test Stat',
      value: 100,
      change: 5,
      trend: 'up',
      color: 'blue',
      icon: 'UserGroupIcon'
      // chartData missing
    }];
    
    renderWithI18n(<AdvancedStats stats={statsWithoutChart} />);
    
    expect(screen.getByText('Test Stat')).toBeInTheDocument();
    expect(screen.getByText('100')).toBeInTheDocument();
  });

  test('formats large numbers correctly', () => {
    const largeNumberStats = [{
      id: 1,
      title: 'Large Number',
      value: 1234567,
      change: 0,
      trend: 'stable',
      color: 'blue',
      icon: 'UserGroupIcon'
    }];
    
    renderWithI18n(<AdvancedStats stats={largeNumberStats} />);
    
    expect(screen.getByText('1,234,567')).toBeInTheDocument();
  });

  test('handles currency formatting', () => {
    renderWithI18n(<AdvancedStats stats={mockStats} />);
    
    expect(screen.getByText('$52,400')).toBeInTheDocument();
  });

  test('shows loading state', () => {
    renderWithI18n(<AdvancedStats stats={mockStats} loading={true} />);
    
    const skeletons = screen.getAllByTestId(/skeleton-/);
    expect(skeletons.length).toBeGreaterThan(0);
  });

  test('handles dark mode correctly', () => {
    renderWithI18n(<AdvancedStats stats={mockStats} darkMode={true} />);
    
    const container = screen.getByTestId('advanced-stats-container');
    expect(container).toHaveClass('dark');
  });

  test('card hover effects work', () => {
    renderWithI18n(<AdvancedStats stats={mockStats} />);
    
    const firstCard = screen.getByTestId('stat-card-1');
    
    fireEvent.mouseEnter(firstCard);
    expect(firstCard).toHaveClass('transform', 'scale-105');
    
    fireEvent.mouseLeave(firstCard);
    expect(firstCard).toHaveClass('transform', 'scale-100');
  });

  test('responsive grid layout', () => {
    renderWithI18n(<AdvancedStats stats={mockStats} />);
    
    const grid = screen.getByTestId('stats-grid');
    expect(grid).toHaveClass('grid', 'grid-cols-1', 'md:grid-cols-2', 'lg:grid-cols-3');
  });

  test('accessibility features', () => {
    renderWithI18n(<AdvancedStats stats={mockStats} />);
    
    const cards = screen.getAllByRole('article');
    expect(cards).toHaveLength(3);
    
    cards.forEach(card => {
      expect(card).toHaveAttribute('tabIndex', '0');
    });
  });

  test('animation timing is correct', () => {
    renderWithI18n(<AdvancedStats stats={mockStats} />);
    
    const cards = screen.getAllByTestId(/stat-card-/);
    
    cards.forEach((card, index) => {
      const style = window.getComputedStyle(card);
      expect(style.animationDelay).toBe(`${index * 0.1}s`);
    });
  });

  test('handles stat updates correctly', () => {
    const { rerender } = renderWithI18n(<AdvancedStats stats={mockStats} />);
    
    expect(screen.getByText('1,250')).toBeInTheDocument();
    
    const updatedStats = [...mockStats];
    updatedStats[0].value = 1500;
    
    rerender(
      <I18nextProvider i18n={i18n}>
        <AdvancedStats stats={updatedStats} />
      </I18nextProvider>
    );
    
    expect(screen.getByText('1,500')).toBeInTheDocument();
  });

  test('chart canvas is created for each stat', () => {
    renderWithI18n(<AdvancedStats stats={mockStats} />);
    
    const canvases = screen.getAllByRole('img'); // Canvas elements have img role
    expect(canvases.length).toBeGreaterThanOrEqual(3);
  });

  test('handles RTL layout correctly', () => {
    // Mock RTL direction
    document.dir = 'rtl';
    
    renderWithI18n(<AdvancedStats stats={mockStats} />);
    
    const container = screen.getByTestId('advanced-stats-container');
    expect(container).toHaveClass('rtl:space-x-reverse');
    
    // Reset
    document.dir = 'ltr';
  });
});
