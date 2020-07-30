module.exports = {
    preset: 'ts-jest',
    testEnvironment: 'node',
    verbose: true,
    testMatch: [
        "**/tests/**/*.test.js",
        "**/?(*.)+(spec|test).+(ts|tsx|js)"
    ],
    setupFilesAfterEnv: [
        `${process.cwd()}/jest.setup.js`
    ],
    "transform": {
        "^.+\\.(ts|tsx)$": "ts-jest"
    },
};
