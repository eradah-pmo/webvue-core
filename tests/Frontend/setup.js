import '@testing-library/jest-dom';

// Mock InertiaJS
global.route = jest.fn((name, params) => {
  return `/${name.replace('.', '/')}${params ? `/${Object.values(params).join('/')}` : ''}`;
});

global.usePage = jest.fn(() => ({
  props: {
    auth: {
      user: {
        id: 1,
        name: 'Test User',
        email: 'test@example.com',
        roles: ['admin']
      }
    },
    flash: {},
    errors: {}
  }
}));

// Mock Heroicons
jest.mock('@heroicons/react/24/outline', () => ({
  UserGroupIcon: () => <div data-testid="user-group-icon">UserGroupIcon</div>,
  ComputerDesktopIcon: () => <div data-testid="computer-desktop-icon">ComputerDesktopIcon</div>,
  CurrencyDollarIcon: () => <div data-testid="currency-dollar-icon">CurrencyDollarIcon</div>,
  ExclamationTriangleIcon: () => <div data-testid="exclamation-triangle-icon">ExclamationTriangleIcon</div>,
  CheckCircleIcon: () => <div data-testid="check-circle-icon">CheckCircleIcon</div>,
  InformationCircleIcon: () => <div data-testid="information-circle-icon">InformationCircleIcon</div>,
  XMarkIcon: () => <div data-testid="x-mark-icon">XMarkIcon</div>,
  ChartBarIcon: () => <div data-testid="chart-bar-icon">ChartBarIcon</div>,
  ArrowUpIcon: () => <div data-testid="arrow-up-icon">ArrowUpIcon</div>,
  ArrowDownIcon: () => <div data-testid="arrow-down-icon">ArrowDownIcon</div>
}));

jest.mock('@heroicons/react/24/solid', () => ({
  UserGroupIcon: () => <div data-testid="user-group-icon-solid">UserGroupIcon</div>,
  ComputerDesktopIcon: () => <div data-testid="computer-desktop-icon-solid">ComputerDesktopIcon</div>,
  CurrencyDollarIcon: () => <div data-testid="currency-dollar-icon-solid">CurrencyDollarIcon</div>,
  ExclamationTriangleIcon: () => <div data-testid="exclamation-triangle-icon-solid">ExclamationTriangleIcon</div>,
  CheckCircleIcon: () => <div data-testid="check-circle-icon-solid">CheckCircleIcon</div>,
  InformationCircleIcon: () => <div data-testid="information-circle-icon-solid">InformationCircleIcon</div>,
  XMarkIcon: () => <div data-testid="x-mark-icon-solid">XMarkIcon</div>
}));

// Mock window.matchMedia
Object.defineProperty(window, 'matchMedia', {
  writable: true,
  value: jest.fn().mockImplementation(query => ({
    matches: false,
    media: query,
    onchange: null,
    addListener: jest.fn(), // deprecated
    removeListener: jest.fn(), // deprecated
    addEventListener: jest.fn(),
    removeEventListener: jest.fn(),
    dispatchEvent: jest.fn(),
  })),
});

// Mock ResizeObserver
global.ResizeObserver = jest.fn().mockImplementation(() => ({
  observe: jest.fn(),
  unobserve: jest.fn(),
  disconnect: jest.fn(),
}));

// Mock IntersectionObserver
global.IntersectionObserver = jest.fn().mockImplementation(() => ({
  observe: jest.fn(),
  unobserve: jest.fn(),
  disconnect: jest.fn(),
}));

// Mock Canvas API for Chart.js
HTMLCanvasElement.prototype.getContext = jest.fn(() => ({
  fillRect: jest.fn(),
  clearRect: jest.fn(),
  getImageData: jest.fn(() => ({ data: new Array(4) })),
  putImageData: jest.fn(),
  createImageData: jest.fn(() => []),
  setTransform: jest.fn(),
  drawImage: jest.fn(),
  save: jest.fn(),
  fillText: jest.fn(),
  restore: jest.fn(),
  beginPath: jest.fn(),
  moveTo: jest.fn(),
  lineTo: jest.fn(),
  closePath: jest.fn(),
  stroke: jest.fn(),
  translate: jest.fn(),
  scale: jest.fn(),
  rotate: jest.fn(),
  arc: jest.fn(),
  fill: jest.fn(),
  measureText: jest.fn(() => ({ width: 0 })),
  transform: jest.fn(),
  rect: jest.fn(),
  clip: jest.fn(),
}));

// Mock localStorage
const localStorageMock = {
  getItem: jest.fn(),
  setItem: jest.fn(),
  removeItem: jest.fn(),
  clear: jest.fn(),
};
global.localStorage = localStorageMock;

// Mock sessionStorage
const sessionStorageMock = {
  getItem: jest.fn(),
  setItem: jest.fn(),
  removeItem: jest.fn(),
  clear: jest.fn(),
};
global.sessionStorage = sessionStorageMock;

// Console error suppression for known issues
const originalError = console.error;
beforeAll(() => {
  console.error = (...args) => {
    if (
      typeof args[0] === 'string' &&
      args[0].includes('Warning: ReactDOM.render is no longer supported')
    ) {
      return;
    }
    originalError.call(console, ...args);
  };
});

afterAll(() => {
  console.error = originalError;
});
