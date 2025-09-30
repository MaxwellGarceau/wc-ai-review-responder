/** @type {import('ts-jest').JestConfigWithTsJest} */
module.exports = {
  preset: 'ts-jest',
  testEnvironment: 'jsdom',
  setupFilesAfterEnv: ['<rootDir>/tests/js/setup.ts'],
  transform: {
    '\\.html$': '<rootDir>/jest-html-transformer.js',
  },
  moduleNameMapper: {
		'\\.(scss)$': 'identity-obj-proxy',
	},
};
