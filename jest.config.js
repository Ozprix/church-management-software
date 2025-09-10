module.exports = {
  testEnvironment: 'jsdom',
  moduleFileExtensions: ['js', 'vue'],
  moduleNameMapper: {
    '^@/(.*)$': '<rootDir>/resources/js/$1'
  },
  transform: {
    '^.+\\.vue$': 'vue-jest',
    '^.+\\.js$': 'babel-jest'
  },
  testMatch: [
    '**/tests/js/**/*.spec.js',
    '**/resources/js/**/*.spec.js'
  ],
  collectCoverage: true,
  collectCoverageFrom: [
    'resources/js/**/*.{js,vue}',
    '!resources/js/app.js',
    '!**/node_modules/**'
  ],
  coverageReporters: ['text', 'html'],
  testPathIgnorePatterns: [
    '/node_modules/',
    '/vendor/'
  ]
};
