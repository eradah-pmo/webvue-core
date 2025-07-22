import React from 'react';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import '@testing-library/jest-dom';
import { I18nextProvider } from 'react-i18next';
import i18n from '../../../__mocks__/i18n';
import StatsChart from '../../../../resources/js/Components/Charts/StatsChart';

// Mock Chart.js
const mockChart = {
  destroy: jest.fn(),
  update: jest.fn(),
  resize: jest.fn(),
  data: { datasets: [] },
  options: {}
};

jest.mock('chart.js/auto', () => ({
  Chart: jest.fn().mockImplementation(() => mockChart)
}));

const mockChartData = {
  labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
  datasets: [
    {
      label: 'Users',
      data: [100, 120, 110, 140, 130, 150],
      borderColor: 'rgb(59, 130, 246)',
      backgroundColor: 'rgba(59, 130, 246, 0.1)',
      tension: 0.4
    },
    {
      label: 'Revenue',
      data: [5000, 6000, 5500, 7000, 6500, 7500],
      borderColor: 'rgb(16, 185, 129)',
      backgroundColor: 'rgba(16, 185, 129, 0.1)',
      tension: 0.4
    }
  ]
};

const renderWithI18n = (component) => {
  return render(
    <I18nextProvider i18n={i18n}>
      {component}
    </I18nextProvider>
  );
};

describe('StatsChart Component', () => {
  beforeEach(() => {
    jest.clearAllMocks();
  });

  test('renders chart container correctly', () => {
    renderWithI18n(
      <StatsChart 
        data={mockChartData}
        type="line"
        title="Test Chart"
      />
    );
    
    expect(screen.getByText('Test Chart')).toBeInTheDocument();
    expect(screen.getByTestId('stats-chart-container')).toBeInTheDocument();
  });

  test('creates chart with correct type', () => {
    const { Chart } = require('chart.js/auto');
    
    renderWithI18n(
      <StatsChart 
        data={mockChartData}
        type="bar"
        title="Bar Chart"
      />
    );
    
    expect(Chart).toHaveBeenCalledWith(
      expect.any(HTMLCanvasElement),
      expect.objectContaining({
        type: 'bar',
        data: mockChartData
      })
    );
  });

  test('handles different chart types', () => {
    const chartTypes = ['line', 'bar', 'area', 'pie', 'doughnut'];
    const { Chart } = require('chart.js/auto');
    
    chartTypes.forEach(type => {
      Chart.mockClear();
      
      const { unmount } = renderWithI18n(
        <StatsChart 
          data={mockChartData}
          type={type}
          title={`${type} Chart`}
        />
      );
      
      expect(Chart).toHaveBeenCalledWith(
        expect.any(HTMLCanvasElement),
        expect.objectContaining({ type })
      );
      
      unmount();
    });
  });

  test('displays trend indicator correctly', () => {
    renderWithI18n(
      <StatsChart 
        data={mockChartData}
        type="line"
        title="Trending Chart"
        trend={{ direction: 'up', percentage: 12.5 }}
      />
    );
    
    expect(screen.getByText('12.5%')).toBeInTheDocument();
    expect(screen.getByTestId('arrow-up-icon')).toBeInTheDocument();
  });

  test('handles downward trend correctly', () => {
    renderWithI18n(
      <StatsChart 
        data={mockChartData}
        type="line"
        title="Declining Chart"
        trend={{ direction: 'down', percentage: 5.2 }}
      />
    );
    
    expect(screen.getByText('5.2%')).toBeInTheDocument();
    expect(screen.getByTestId('arrow-down-icon')).toBeInTheDocument();
  });

  test('shows loading state', () => {
    renderWithI18n(
      <StatsChart 
        data={mockChartData}
        type="line"
        title="Loading Chart"
        loading={true}
      />
    );
    
    expect(screen.getByTestId('chart-skeleton')).toBeInTheDocument();
  });

  test('handles empty data gracefully', () => {
    renderWithI18n(
      <StatsChart 
        data={{ labels: [], datasets: [] }}
        type="line"
        title="Empty Chart"
      />
    );
    
    expect(screen.getByText(/no.*data.*available/i)).toBeInTheDocument();
  });

  test('applies dark mode styling', () => {
    renderWithI18n(
      <StatsChart 
        data={mockChartData}
        type="line"
        title="Dark Chart"
        darkMode={true}
      />
    );
    
    const container = screen.getByTestId('stats-chart-container');
    expect(container).toHaveClass('dark');
  });

  test('chart updates when data changes', () => {
    const { rerender } = renderWithI18n(
      <StatsChart 
        data={mockChartData}
        type="line"
        title="Dynamic Chart"
      />
    );
    
    const newData = {
      ...mockChartData,
      datasets: [
        {
          ...mockChartData.datasets[0],
          data: [200, 220, 210, 240, 230, 250]
        }
      ]
    };
    
    rerender(
      <I18nextProvider i18n={i18n}>
        <StatsChart 
          data={newData}
          type="line"
          title="Dynamic Chart"
        />
      </I18nextProvider>
    );
    
    expect(mockChart.update).toHaveBeenCalled();
  });

  test('chart is destroyed on unmount', () => {
    const { unmount } = renderWithI18n(
      <StatsChart 
        data={mockChartData}
        type="line"
        title="Unmount Chart"
      />
    );
    
    unmount();
    
    expect(mockChart.destroy).toHaveBeenCalled();
  });

  test('handles responsive behavior', () => {
    renderWithI18n(
      <StatsChart 
        data={mockChartData}
        type="line"
        title="Responsive Chart"
        responsive={true}
      />
    );
    
    const canvas = screen.getByRole('img');
    expect(canvas.parentElement).toHaveClass('relative');
  });

  test('displays chart options correctly', () => {
    const customOptions = {
      plugins: {
        legend: {
          display: false
        }
      }
    };
    
    const { Chart } = require('chart.js/auto');
    
    renderWithI18n(
      <StatsChart 
        data={mockChartData}
        type="line"
        title="Custom Options Chart"
        options={customOptions}
      />
    );
    
    expect(Chart).toHaveBeenCalledWith(
      expect.any(HTMLCanvasElement),
      expect.objectContaining({
        options: expect.objectContaining(customOptions)
      })
    );
  });

  test('handles chart click events', () => {
    const mockOnClick = jest.fn();
    
    renderWithI18n(
      <StatsChart 
        data={mockChartData}
        type="line"
        title="Clickable Chart"
        onClick={mockOnClick}
      />
    );
    
    const canvas = screen.getByRole('img');
    fireEvent.click(canvas);
    
    expect(mockOnClick).toHaveBeenCalled();
  });

  test('shows chart legend when enabled', () => {
    renderWithI18n(
      <StatsChart 
        data={mockChartData}
        type="line"
        title="Chart with Legend"
        showLegend={true}
      />
    );
    
    expect(screen.getByTestId('chart-legend')).toBeInTheDocument();
  });

  test('handles chart export functionality', async () => {
    const mockToDataURL = jest.fn().mockReturnValue('data:image/png;base64,test');
    HTMLCanvasElement.prototype.toDataURL = mockToDataURL;
    
    renderWithI18n(
      <StatsChart 
        data={mockChartData}
        type="line"
        title="Exportable Chart"
        showExport={true}
      />
    );
    
    const exportButton = screen.getByText(/export/i);
    fireEvent.click(exportButton);
    
    await waitFor(() => {
      expect(mockToDataURL).toHaveBeenCalledWith('image/png');
    });
  });

  test('applies custom height and width', () => {
    renderWithI18n(
      <StatsChart 
        data={mockChartData}
        type="line"
        title="Custom Size Chart"
        height={400}
        width={600}
      />
    );
    
    const container = screen.getByTestId('stats-chart-container');
    expect(container.style.height).toBe('400px');
    expect(container.style.width).toBe('600px');
  });

  test('handles animation settings', () => {
    const { Chart } = require('chart.js/auto');
    
    renderWithI18n(
      <StatsChart 
        data={mockChartData}
        type="line"
        title="Animated Chart"
        animated={false}
      />
    );
    
    expect(Chart).toHaveBeenCalledWith(
      expect.any(HTMLCanvasElement),
      expect.objectContaining({
        options: expect.objectContaining({
          animation: false
        })
      })
    );
  });

  test('displays chart subtitle when provided', () => {
    renderWithI18n(
      <StatsChart 
        data={mockChartData}
        type="line"
        title="Main Title"
        subtitle="Chart Subtitle"
      />
    );
    
    expect(screen.getByText('Main Title')).toBeInTheDocument();
    expect(screen.getByText('Chart Subtitle')).toBeInTheDocument();
  });

  test('handles RTL layout correctly', () => {
    document.dir = 'rtl';
    
    renderWithI18n(
      <StatsChart 
        data={mockChartData}
        type="line"
        title="RTL Chart"
      />
    );
    
    const container = screen.getByTestId('stats-chart-container');
    expect(container).toHaveClass('rtl:space-x-reverse');
    
    document.dir = 'ltr';
  });

  test('accessibility features work correctly', () => {
    renderWithI18n(
      <StatsChart 
        data={mockChartData}
        type="line"
        title="Accessible Chart"
        ariaLabel="Statistics chart showing user growth"
      />
    );
    
    const canvas = screen.getByRole('img');
    expect(canvas).toHaveAttribute('aria-label', 'Statistics chart showing user growth');
  });
});
