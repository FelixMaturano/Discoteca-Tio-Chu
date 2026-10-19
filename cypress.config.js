import { spawn, spawnSync } from 'node:child_process';
import { get as httpGet } from 'node:http';
import { createServer } from 'node:net';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { mkdirSync, writeFileSync } from 'node:fs';

const projectRoot = dirname(fileURLToPath(import.meta.url));
const port = 8017;
const baseUrl = `http://127.0.0.1:${port}`;
const databasePath = resolve(projectRoot, 'storage/framework/testing/cypress.sqlite');
const testEnvironment = {
    ...process.env,
    APP_ENV: 'testing',
    APP_URL: baseUrl,
    DB_CONNECTION: 'sqlite',
    DB_DATABASE: databasePath,
    SESSION_DRIVER: 'file',
    CACHE_STORE: 'file',
    QUEUE_CONNECTION: 'sync',
    MAIL_MAILER: 'array',
};

let serverProcess;

function isLaravelReady() {
    return new Promise((resolveReady) => {
        const request = httpGet(`${baseUrl}/login`, (response) => {
            response.resume();
            resolveReady(response.statusCode === 200);
        });
        request.setTimeout(1000, () => request.destroy());
        request.on('error', () => resolveReady(false));
    });
}

function runArtisan(args) {
    const result = spawnSync('php', ['artisan', ...args], {
        cwd: projectRoot,
        env: testEnvironment,
        encoding: 'utf8',
    });

    if (result.error || result.status !== 0) {
        throw new Error([
            `php artisan ${args.join(' ')} failed.`,
            result.error?.message,
            result.stdout,
            result.stderr,
        ].filter(Boolean).join('\n'));
    }
}

function prepareTestDatabase() {
    runArtisan(['migrate:fresh', '--force']);
    for (const seeder of ['UserSeeder', 'ProductSeeder', 'StaffSeeder']) {
        runArtisan(['db:seed', `--class=${seeder}`, '--force']);
    }
}

function assertPortAvailable() {
    return new Promise((resolvePort, rejectPort) => {
        const probe = createServer();
        probe.once('error', rejectPort);
        probe.listen(port, '127.0.0.1', () => probe.close(resolvePort));
    });
}

async function startLaravel() {
    try {
        await assertPortAvailable();
    } catch (error) {
        if (error.code === 'EADDRINUSE' && await isLaravelReady()) return;
        throw error;
    }

    serverProcess = spawn('php', ['artisan', 'serve', '--host=127.0.0.1', `--port=${port}`], {
        cwd: projectRoot,
        env: testEnvironment,
        stdio: 'ignore',
        windowsHide: true,
    });

    const deadline = Date.now() + 30000;
    while (Date.now() < deadline) {
        if (serverProcess.exitCode !== null) {
            throw new Error(`Laravel server exited with code ${serverProcess.exitCode}.`);
        }

        if (await isLaravelReady()) return;

        await new Promise((resolveWait) => setTimeout(resolveWait, 250));
    }

    throw new Error(`Laravel did not become ready at ${baseUrl}.`);
}

function stopLaravel() {
    if (serverProcess && serverProcess.exitCode === null) {
        serverProcess.kill();
    }
    serverProcess = undefined;
}

mkdirSync(dirname(databasePath), { recursive: true });
writeFileSync(databasePath, '', { flag: 'a' });
prepareTestDatabase();

process.on('exit', stopLaravel);

export default {
    e2e: {
        baseUrl,
        specPattern: 'cypress/e2e/**/*.cy.js',
        supportFile: 'cypress/support/e2e.js',
        defaultCommandTimeout: 10000,
        setupNodeEvents(on) {
            on('before:spec', prepareTestDatabase);
            on('after:run', stopLaravel);
            return startLaravel();
        },
    },
};