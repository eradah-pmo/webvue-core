import React from 'react';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import '@testing-library/jest-dom';
import { I18nextProvider } from 'react-i18next';
import i18n from '../../../__mocks__/i18n';
import SystemAlerts from '../../../../resources/js/Components/Dashboard/SystemAlerts';

const mockAlerts = [
  {
    id: 1,
    type: 'error',
    title: 'System Error',
    message: 'Database connection failed',
    timestamp: '2024-01-15T10:30:00Z',
    dismissible: true,
    actions: [
      { label: 'Retry', action: 'retry', variant: 'primary' },
      { label: 'Details', action: 'details', variant: 'secondary' }
    ]
  },
  {
    id: 2,
    type: 'warning',
    title: 'Storage Warning',
    message: 'Disk space is running low (85% used)',
    timestamp: '2024-01-15T09:15:00Z',
    dismissible: true,
    actions: [
      { label: 'Clean Up', action: 'cleanup', variant: 'warning' }
    ]
  },
  {
    id: 3,
    type: 'success',
    title: 'Backup Complete',
    message: 'Daily backup completed successfully',
    timestamp: '2024-01-15T08:00:00Z',
    dismissible: true
  },
  {
    id: 4,
    type: 'info',
    title: 'Maintenance Scheduled',
    message: 'System maintenance scheduled for tonight at 2 AM',
    timestamp: '2024-01-15T07:45:00Z',
    dismissible: false
  }
];

const renderWithI18n = (component) => {
  return render(
    <I18nextProvider i18n={i18n}>
      {component}
    </I18nextProvider>
  );
};

describe('SystemAlerts Component', () => {
  const mockOnDismiss = jest.fn();
  const mockOnAction = jest.fn();

  beforeEach(() => {
    jest.clearAllMocks();
  });

  test('renders all alerts correctly', () => {
    renderWithI18n(
      <SystemAlerts 
        alerts={mockAlerts} 
        onDismiss={mockOnDismiss}
        onAction={mockOnAction}
      />
    );
    
    expect(screen.getByText('System Error')).toBeInTheDocument();
    expect(screen.getByText('Storage Warning')).toBeInTheDocument();
    expect(screen.getByText('Backup Complete')).toBeInTheDocument();
    expect(screen.getByText('Maintenance Scheduled')).toBeInTheDocument();
  });

  test('displays correct alert types with appropriate styling', () => {
    renderWithI18n(
      <SystemAlerts 
        alerts={mockAlerts} 
        onDismiss={mockOnDismiss}
        onAction={mockOnAction}
      />
    );
    
    const errorAlert = screen.getByTestId('alert-1');
    const warningAlert = screen.getByTestId('alert-2');
    const successAlert = screen.getByTestId('alert-3');
    const infoAlert = screen.getByTestId('alert-4');
    
    expect(errorAlert).toHaveClass('border-red-200');
    expect(warningAlert).toHaveClass('border-yellow-200');
    expect(successAlert).toHaveClass('border-green-200');
    expect(infoAlert).toHaveClass('border-blue-200');
  });

  test('shows dismiss button only for dismissible alerts', () => {
    renderWithI18n(
      <SystemAlerts 
        alerts={mockAlerts} 
        onDismiss={mockOnDismiss}
        onAction={mockOnAction}
      />
    );
    
    const dismissButtons = screen.getAllByLabelText(/dismiss/i);
    expect(dismissButtons).toHaveLength(3); // 3 dismissible alerts
  });

  test('calls onDismiss when dismiss button is clicked', async () => {
    renderWithI18n(
      <SystemAlerts 
        alerts={mockAlerts} 
        onDismiss={mockOnDismiss}
        onAction={mockOnAction}
      />
    );
    
    const firstDismissButton = screen.getAllByLabelText(/dismiss/i)[0];
    fireEvent.click(firstDismissButton);
    
    await waitFor(() => {
      expect(mockOnDismiss).toHaveBeenCalledWith(1);
    });
  });

  test('renders action buttons correctly', () => {
    renderWithI18n(
      <SystemAlerts 
        alerts={mockAlerts} 
        onDismiss={mockOnDismiss}
        onAction={mockOnAction}
      />
    );
    
    expect(screen.getByText('Retry')).toBeInTheDocument();
    expect(screen.getByText('Details')).toBeInTheDocument();
    expect(screen.getByText('Clean Up')).toBeInTheDocument();
  });

  test('calls onAction when action button is clicked', async () => {
    renderWithI18n(
      <SystemAlerts 
        alerts={mockAlerts} 
        onDismiss={mockOnDismiss}
        onAction={mockOnAction}
      />
    );
    
    const retryButton = screen.getByText('Retry');
    fireEvent.click(retryButton);
    
    await waitFor(() => {
      expect(mockOnAction).toHaveBeenCalledWith(1, 'retry');
    });
  });

  test('formats timestamps correctly', () => {
    renderWithI18n(
      <SystemAlerts 
        alerts={mockAlerts} 
        onDismiss={mockOnDismiss}
        onAction={mockOnAction}
      />
    );
    
    // التحقق من وجود أوقات منسقة
    const timestamps = screen.getAllByText(/ago|AM|PM/);
    expect(timestamps.length).toBeGreaterThan(0);
  });

  test('handles empty alerts array', () => {
    renderWithI18n(
      <SystemAlerts 
        alerts={[]} 
        onDismiss={mockOnDismiss}
        onAction={mockOnAction}
      />
    );
    
    expect(screen.getByText(/no.*alerts/i)).toBeInTheDocument();
  });

  test('shows loading state', () => {
    renderWithI18n(
      <SystemAlerts 
        alerts={mockAlerts} 
        loading={true}
        onDismiss={mockOnDismiss}
        onAction={mockOnAction}
      />
    );
    
    const skeletons = screen.getAllByTestId(/skeleton/);
    expect(skeletons.length).toBeGreaterThan(0);
  });

  test('alert animations work correctly', async () => {
    const { rerender } = renderWithI18n(
      <SystemAlerts 
        alerts={mockAlerts} 
        onDismiss={mockOnDismiss}
        onAction={mockOnAction}
      />
    );
    
    // محاكاة إزالة تنبيه
    const updatedAlerts = mockAlerts.filter(alert => alert.id !== 1);
    
    rerender(
      <I18nextProvider i18n={i18n}>
        <SystemAlerts 
          alerts={updatedAlerts} 
          onDismiss={mockOnDismiss}
          onAction={mockOnAction}
        />
      </I18nextProvider>
    );
    
    await waitFor(() => {
      expect(screen.queryByText('System Error')).not.toBeInTheDocument();
    });
  });

  test('handles dark mode correctly', () => {
    renderWithI18n(
      <SystemAlerts 
        alerts={mockAlerts} 
        darkMode={true}
        onDismiss={mockOnDismiss}
        onAction={mockOnAction}
      />
    );
    
    const container = screen.getByTestId('system-alerts-container');
    expect(container).toHaveClass('dark');
  });

  test('alert priority ordering works', () => {
    const priorityAlerts = [
      { ...mockAlerts[0], priority: 'high' },
      { ...mockAlerts[1], priority: 'medium' },
      { ...mockAlerts[2], priority: 'low' }
    ];
    
    renderWithI18n(
      <SystemAlerts 
        alerts={priorityAlerts} 
        onDismiss={mockOnDismiss}
        onAction={mockOnAction}
      />
    );
    
    const alertElements = screen.getAllByTestId(/alert-/);
    expect(alertElements[0]).toHaveClass('ring-2'); // High priority
  });

  test('accessibility features work correctly', () => {
    renderWithI18n(
      <SystemAlerts 
        alerts={mockAlerts} 
        onDismiss={mockOnDismiss}
        onAction={mockOnAction}
      />
    );
    
    const alerts = screen.getAllByRole('alert');
    expect(alerts).toHaveLength(4);
    
    const buttons = screen.getAllByRole('button');
    buttons.forEach(button => {
      expect(button).toHaveAttribute('type', 'button');
    });
  });

  test('keyboard navigation works', () => {
    renderWithI18n(
      <SystemAlerts 
        alerts={mockAlerts} 
        onDismiss={mockOnDismiss}
        onAction={mockOnAction}
      />
    );
    
    const firstButton = screen.getAllByRole('button')[0];
    firstButton.focus();
    
    fireEvent.keyDown(firstButton, { key: 'Enter' });
    expect(mockOnAction).toHaveBeenCalled();
  });

  test('auto-dismiss functionality works', async () => {
    const autoDismissAlerts = [
      { ...mockAlerts[0], autoDismiss: true, autoDismissDelay: 100 }
    ];
    
    renderWithI18n(
      <SystemAlerts 
        alerts={autoDismissAlerts} 
        onDismiss={mockOnDismiss}
        onAction={mockOnAction}
      />
    );
    
    await waitFor(() => {
      expect(mockOnDismiss).toHaveBeenCalledWith(1);
    }, { timeout: 200 });
  });

  test('alert grouping by type works', () => {
    renderWithI18n(
      <SystemAlerts 
        alerts={mockAlerts} 
        groupByType={true}
        onDismiss={mockOnDismiss}
        onAction={mockOnAction}
      />
    );
    
    const groups = screen.getAllByTestId(/alert-group-/);
    expect(groups.length).toBeGreaterThan(0);
  });

  test('handles RTL layout correctly', () => {
    document.dir = 'rtl';
    
    renderWithI18n(
      <SystemAlerts 
        alerts={mockAlerts} 
        onDismiss={mockOnDismiss}
        onAction={mockOnAction}
      />
    );
    
    const container = screen.getByTestId('system-alerts-container');
    expect(container).toHaveClass('rtl:space-x-reverse');
    
    document.dir = 'ltr';
  });
});
