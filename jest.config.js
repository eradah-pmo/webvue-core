module.exports = {
  testEnvironment: 'jsdom',
  setupFilesAfterEnv: ['<rootDir>/tests/Frontend/setup.js'],
  testMatch: [
    '<rootDir>/tests/Frontend/**/*.test.{js,jsx}',
    '<rootDir>/tests/Frontend/**/*.spec.{js,jsx}'
  ],
  moduleNameMapping: {
    '^@/(.*)$': '<rootDir>/resources/js/$1',
    '^@Components/(.*)$': '<rootDir>/resources/js/Components/$1',
    '^@Pages/(.*)$': '<rootDir>/resources/js/Pages/$1',
    '^@Layouts/(.*)$': '<rootDir>/resources/js/Layouts/$1',
    '^@Utils/(.*)$': '<rootDir>/resources/js/Utils/$1',
    '\\.(css|less|scss|sass)$': 'identity-obj-proxy'
  },
  transform: {
    '^.+\\.(js|jsx)$': ['babel-jest', {
      presets: [
        ['@babel/preset-env', { targets: { node: 'current' } }],
        ['@babel/preset-react', { runtime: 'automatic' }]
      ]
    }]
  },
  moduleFileExtensions: ['js', 'jsx', 'json'],
  collectCoverageFrom: [
    'resources/js/Components/**/*.{js,jsx}',
    'resources/js/Pages/**/*.{js,jsx}',
    'resources/js/Layouts/**/*.{js,jsx}',
    '!resources/js/**/*.stories.{js,jsx}',
    '!resources/js/**/*.d.ts'
  ],
  coverageReporters: ['text', 'lcov', 'html'],
  coverageDirectory: 'coverage/frontend',
  coverageThreshold: {
    global: {
      branches: 80,
      functions: 80,
      lines: 80,
      statements: 80
    }
  },
  testTimeout: 10000,
  verbose: true,
  clearMocks: true,
  restoreMocks: true
};
