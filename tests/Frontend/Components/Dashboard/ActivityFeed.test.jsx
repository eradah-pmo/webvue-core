import React from 'react';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import '@testing-library/jest-dom';
import { I18nextProvider } from 'react-i18next';
import i18n from '../../../__mocks__/i18n';
import ActivityFeed from '../../../../resources/js/Components/Dashboard/ActivityFeed';

const mockActivities = [
  {
    id: 1,
    type: 'user_created',
    title: 'New user registered',
    description: 'John Doe created a new account',
    user: { name: 'John Doe', avatar: '/avatars/john.jpg' },
    timestamp: '2024-01-15T10:30:00Z',
    icon: 'UserPlusIcon',
    color: 'green'
  },
  {
    id: 2,
    type: 'login',
    title: 'User login',
    description: 'Jane Smith logged into the system',
    user: { name: 'Jane Smith', avatar: '/avatars/jane.jpg' },
    timestamp: '2024-01-15T09:15:00Z',
    icon: 'ArrowRightOnRectangleIcon',
    color: 'blue'
  },
  {
    id: 3,
    type: 'file_upload',
    title: 'File uploaded',
    description: 'Document.pdf was uploaded to the system',
    user: { name: 'Admin User', avatar: '/avatars/admin.jpg' },
    timestamp: '2024-01-15T08:45:00Z',
    icon: 'DocumentArrowUpIcon',
    color: 'purple'
  },
  {
    id: 4,
    type: 'settings_updated',
    title: 'Settings changed',
    description: 'System settings were updated',
    user: { name: 'Super Admin', avatar: '/avatars/super.jpg' },
    timestamp: '2024-01-15T08:00:00Z',
    icon: 'CogIcon',
    color: 'orange'
  }
];

const renderWithI18n = (component) => {
  return render(
    <I18nextProvider i18n={i18n}>
      {component}
    </I18nextProvider>
  );
};

describe('ActivityFeed Component', () => {
  const mockOnLoadMore = jest.fn();
  const mockOnFilter = jest.fn();

  beforeEach(() => {
    jest.clearAllMocks();
  });

  test('renders activity feed correctly', () => {
    renderWithI18n(
      <ActivityFeed 
        activities={mockActivities}
        onLoadMore={mockOnLoadMore}
        onFilter={mockOnFilter}
      />
    );
    
    expect(screen.getByText('New user registered')).toBeInTheDocument();
    expect(screen.getByText('User login')).toBeInTheDocument();
    expect(screen.getByText('File uploaded')).toBeInTheDocument();
    expect(screen.getByText('Settings changed')).toBeInTheDocument();
  });

  test('displays user information correctly', () => {
    renderWithI18n(
      <ActivityFeed 
        activities={mockActivities}
        onLoadMore={mockOnLoadMore}
        onFilter={mockOnFilter}
      />
    );
    
    expect(screen.getByText('John Doe')).toBeInTheDocument();
    expect(screen.getByText('Jane Smith')).toBeInTheDocument();
    expect(screen.getByText('Admin User')).toBeInTheDocument();
    expect(screen.getByText('Super Admin')).toBeInTheDocument();
  });

  test('shows activity descriptions', () => {
    renderWithI18n(
      <ActivityFeed 
        activities={mockActivities}
        onLoadMore={mockOnLoadMore}
        onFilter={mockOnFilter}
      />
    );
    
    expect(screen.getByText('John Doe created a new account')).toBeInTheDocument();
    expect(screen.getByText('Jane Smith logged into the system')).toBeInTheDocument();
    expect(screen.getByText('Document.pdf was uploaded to the system')).toBeInTheDocument();
    expect(screen.getByText('System settings were updated')).toBeInTheDocument();
  });

  test('formats timestamps correctly', () => {
    renderWithI18n(
      <ActivityFeed 
        activities={mockActivities}
        onLoadMore={mockOnLoadMore}
        onFilter={mockOnFilter}
      />
    );
    
    // التحقق من وجود أوقات منسقة
    const timeElements = screen.getAllByText(/ago|AM|PM|minutes|hours|days/);
    expect(timeElements.length).toBeGreaterThan(0);
  });

  test('displays activity icons correctly', () => {
    renderWithI18n(
      <ActivityFeed 
        activities={mockActivities}
        onLoadMore={mockOnLoadMore}
        onFilter={mockOnFilter}
      />
    );
    
    const activityItems = screen.getAllByTestId(/activity-item-/);
    expect(activityItems).toHaveLength(4);
    
    // التحقق من وجود الأيقونات
    activityItems.forEach(item => {
      const icon = item.querySelector('[data-testid*="icon"]');
      expect(icon).toBeInTheDocument();
    });
  });

  test('applies correct color themes', () => {
    renderWithI18n(
      <ActivityFeed 
        activities={mockActivities}
        onLoadMore={mockOnLoadMore}
        onFilter={mockOnFilter}
      />
    );
    
    const activityItems = screen.getAllByTestId(/activity-item-/);
    
    expect(activityItems[0]).toHaveClass('border-l-green-500');
    expect(activityItems[1]).toHaveClass('border-l-blue-500');
    expect(activityItems[2]).toHaveClass('border-l-purple-500');
    expect(activityItems[3]).toHaveClass('border-l-orange-500');
  });

  test('handles empty activities list', () => {
    renderWithI18n(
      <ActivityFeed 
        activities={[]}
        onLoadMore={mockOnLoadMore}
        onFilter={mockOnFilter}
      />
    );
    
    expect(screen.getByText(/no.*activities.*found/i)).toBeInTheDocument();
  });

  test('shows loading state', () => {
    renderWithI18n(
      <ActivityFeed 
        activities={mockActivities}
        loading={true}
        onLoadMore={mockOnLoadMore}
        onFilter={mockOnFilter}
      />
    );
    
    const skeletons = screen.getAllByTestId(/skeleton/);
    expect(skeletons.length).toBeGreaterThan(0);
  });

  test('filter functionality works', () => {
    renderWithI18n(
      <ActivityFeed 
        activities={mockActivities}
        onLoadMore={mockOnLoadMore}
        onFilter={mockOnFilter}
        showFilter={true}
      />
    );
    
    const filterSelect = screen.getByRole('combobox');
    fireEvent.change(filterSelect, { target: { value: 'user_created' } });
    
    expect(mockOnFilter).toHaveBeenCalledWith('user_created');
  });

  test('load more functionality works', () => {
    renderWithI18n(
      <ActivityFeed 
        activities={mockActivities}
        hasMore={true}
        onLoadMore={mockOnLoadMore}
        onFilter={mockOnFilter}
      />
    );
    
    const loadMoreButton = screen.getByText(/load.*more/i);
    fireEvent.click(loadMoreButton);
    
    expect(mockOnLoadMore).toHaveBeenCalled();
  });

  test('handles dark mode correctly', () => {
    renderWithI18n(
      <ActivityFeed 
        activities={mockActivities}
        darkMode={true}
        onLoadMore={mockOnLoadMore}
        onFilter={mockOnFilter}
      />
    );
    
    const container = screen.getByTestId('activity-feed-container');
    expect(container).toHaveClass('dark');
  });

  test('activity item animations work', () => {
    renderWithI18n(
      <ActivityFeed 
        activities={mockActivities}
        onLoadMore={mockOnLoadMore}
        onFilter={mockOnFilter}
      />
    );
    
    const activityItems = screen.getAllByTestId(/activity-item-/);
    
    activityItems.forEach((item, index) => {
      expect(item).toHaveClass('animate-fade-in');
      expect(item.style.animationDelay).toBe(`${index * 0.1}s`);
    });
  });

  test('handles activity click events', () => {
    const mockOnActivityClick = jest.fn();
    
    renderWithI18n(
      <ActivityFeed 
        activities={mockActivities}
        onActivityClick={mockOnActivityClick}
        onLoadMore={mockOnLoadMore}
        onFilter={mockOnFilter}
      />
    );
    
    const firstActivity = screen.getByTestId('activity-item-1');
    fireEvent.click(firstActivity);
    
    expect(mockOnActivityClick).toHaveBeenCalledWith(mockActivities[0]);
  });

  test('shows user avatars when available', () => {
    renderWithI18n(
      <ActivityFeed 
        activities={mockActivities}
        onLoadMore={mockOnLoadMore}
        onFilter={mockOnFilter}
      />
    );
    
    const avatars = screen.getAllByRole('img');
    expect(avatars.length).toBeGreaterThan(0);
    
    avatars.forEach(avatar => {
      expect(avatar).toHaveAttribute('src');
    });
  });

  test('handles missing user avatars gracefully', () => {
    const activitiesWithoutAvatars = mockActivities.map(activity => ({
      ...activity,
      user: { ...activity.user, avatar: null }
    }));
    
    renderWithI18n(
      <ActivityFeed 
        activities={activitiesWithoutAvatars}
        onLoadMore={mockOnLoadMore}
        onFilter={mockOnFilter}
      />
    );
    
    const defaultAvatars = screen.getAllByTestId(/default-avatar/);
    expect(defaultAvatars.length).toBeGreaterThan(0);
  });

  test('activity grouping by date works', () => {
    renderWithI18n(
      <ActivityFeed 
        activities={mockActivities}
        groupByDate={true}
        onLoadMore={mockOnLoadMore}
        onFilter={mockOnFilter}
      />
    );
    
    const dateGroups = screen.getAllByTestId(/date-group/);
    expect(dateGroups.length).toBeGreaterThan(0);
  });

  test('real-time updates work correctly', async () => {
    const { rerender } = renderWithI18n(
      <ActivityFeed 
        activities={mockActivities}
        onLoadMore={mockOnLoadMore}
        onFilter={mockOnFilter}
      />
    );
    
    const newActivity = {
      id: 5,
      type: 'new_activity',
      title: 'New Activity',
      description: 'A new activity occurred',
      user: { name: 'New User', avatar: '/avatars/new.jpg' },
      timestamp: new Date().toISOString(),
      icon: 'BellIcon',
      color: 'red'
    };
    
    const updatedActivities = [newActivity, ...mockActivities];
    
    rerender(
      <I18nextProvider i18n={i18n}>
        <ActivityFeed 
          activities={updatedActivities}
          onLoadMore={mockOnLoadMore}
          onFilter={mockOnFilter}
        />
      </I18nextProvider>
    );
    
    await waitFor(() => {
      expect(screen.getByText('New Activity')).toBeInTheDocument();
    });
  });

  test('accessibility features work correctly', () => {
    renderWithI18n(
      <ActivityFeed 
        activities={mockActivities}
        onLoadMore={mockOnLoadMore}
        onFilter={mockOnFilter}
      />
    );
    
    const activityItems = screen.getAllByRole('listitem');
    expect(activityItems).toHaveLength(4);
    
    activityItems.forEach(item => {
      expect(item).toHaveAttribute('tabIndex', '0');
    });
  });

  test('keyboard navigation works', () => {
    const mockOnActivityClick = jest.fn();
    
    renderWithI18n(
      <ActivityFeed 
        activities={mockActivities}
        onActivityClick={mockOnActivityClick}
        onLoadMore={mockOnLoadMore}
        onFilter={mockOnFilter}
      />
    );
    
    const firstActivity = screen.getByTestId('activity-item-1');
    firstActivity.focus();
    
    fireEvent.keyDown(firstActivity, { key: 'Enter' });
    expect(mockOnActivityClick).toHaveBeenCalled();
  });

  test('handles RTL layout correctly', () => {
    document.dir = 'rtl';
    
    renderWithI18n(
      <ActivityFeed 
        activities={mockActivities}
        onLoadMore={mockOnLoadMore}
        onFilter={mockOnFilter}
      />
    );
    
    const container = screen.getByTestId('activity-feed-container');
    expect(container).toHaveClass('rtl:space-x-reverse');
    
    document.dir = 'ltr';
  });
});
