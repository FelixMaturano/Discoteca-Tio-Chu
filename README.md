# Tío Chu App

Sistema web para la gestión operativa nocturna de un bar y licorería: apertura y cierre de noches, inventario, ventas por barra y tienda, cobros, nómina, arqueo y consulta ejecutiva.

## Stack y operación

| Componente | Tecnología verificada |
|---|---|
| Backend | PHP 8.4 en CI/Docker; Laravel 12 |
| Persistencia | SQLite por defecto y en PHPUnit; Laravel también configura conexiones MySQL/MariaDB y PostgreSQL |
| Frontend | Blade, Vite 7 y Tailwind CSS 4 |
| Pruebas | PHPUnit 11, Laravel Pint, Cypress 16 y Apache JMeter 5.6.3 |
| Contenedor | `php:8.4-apache`; las migraciones y seeders se ejecutan antes de Apache |
| Despliegue | Render con HTTPS; la aplicación fuerza el esquema HTTPS en producción |

El repositorio contiene el código y los archivos compilados de `public/build/`. Para producción se excluye `public/hot`, que es el marcador de Vite en desarrollo.

## Instalación local

### Requisitos

- PHP 8.4 recomendado (el `composer.json` declara compatibilidad desde PHP 8.2), Composer y extensiones `pdo_sqlite`, `mbstring`, `fileinfo` y `dom`.
- Node.js 20 y npm.
- SQLite para la base local.

### Preparar Laravel

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
```

En `.env`, confirma `DB_CONNECTION=sqlite`, define `APP_URL=http://127.0.0.1:8000` y establece un `ADMIN_PASSWORD` propio. Si el archivo de base de datos aún no existe:

```powershell
New-Item -ItemType File -Path database\database.sqlite -Force
php artisan migrate --seed
```

El seeder carga el catálogo inicial y la cuenta `DonLudo@gmail.chu`; la contraseña se toma de `ADMIN_PASSWORD` (el código conserva `tiochu123` como fallback, que no debe usarse en un despliegue). El `.env` contiene configuración sensible y no debe subirse al repositorio.

### Frontend y servidor

```powershell
npm ci
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

Para desarrollo con recarga de Vite, usa `npm run dev` en otra terminal en lugar de `npm run build`. El CSP permite los hosts localhost de Vite/HMR únicamente en `APP_ENV=local`.

### Pruebas automatizadas

```powershell
php artisan test
.\vendor\bin\pint --test
.\vendor\bin\phpstan analyse --memory-limit=1G --level=5 app
```

Para generar cobertura Clover en una instalación Linux/CI con la extensión PCOV habilitada en PHP CLI:

```sh
mkdir -p coverage
php -d pcov.enabled=1 -d pcov.directory=app vendor/bin/phpunit --coverage-clover=coverage/clover.xml
```

Comprueba `php -m` y habilita/instala PCOV en el PHP CLI antes de ejecutar ese comando. En el entorno Windows usado para este cierre PCOV no estaba cargado; por ello la cobertura de abajo se verificó contra el artefacto `coverage/clover.xml` disponible, no se regeneró localmente.

### Cypress

```powershell
npm ci
npm run cypress:run
```

`cypress.config.js` prepara una base SQLite separada, ejecuta migraciones/seeders y levanta Laravel en `127.0.0.1:8017`; no uses la base de producción para estas pruebas. Las especificaciones están en `cypress/e2e/`.

### JMeter

Levanta primero la aplicación y usa una cuenta de prueba válida; el plan realiza login CSRF por usuario y después consulta dashboard, cierre e inventario:

```sh
jmeter -n -t jmeter/sprint4_performance.jmx \
  -l jmeter/sprint4_results.jtl \
  -Jhost=127.0.0.1 -Jport=8000 \
  -Jusername="$PERF_USER" -Jpassword="$PERF_PASSWORD"
```

Para volver a generar el informe HTML, selecciona una carpeta de salida vacía:

```sh
jmeter -g jmeter/sprint4_results.jtl -o jmeter/reporte_html
```

`jmeter/reporte_html/` es un informe generado e ignorado por Git; el JTL de resultados no está ignorado.

## Métricas verificadas al cierre del Sprint 4

| Indicador | Resultado | Umbral / interpretación |
|---|---:|---|
| PHPUnit | 178 tests, 984 aserciones, 0 fallos | 100% aprobados en `php artisan test` |
| Clover | 2,419 / 3,023 sentencias = 80.02%, 37 archivos | Cumple `>= 80.00%`; fuente: `coverage/clover.xml` |
| JMeter concurrencia / muestras | 50 usuarios / 7,600 muestras | Según `jmeter/sprint4_results.jtl` e informe generado |
| JMeter p95 / promedio | 727.95 ms / 326.56 ms | p95 `<= 1,500 ms` |
| JMeter throughput | 120.90 req/s | Cumple `>= 30 req/s` |
| JMeter fallos / APDEX | 3 / 7,600 = 0.04%; APDEX 0.909 | Cumple `< 1%`; los 3 fallos son aserciones de duración, con respuestas HTTP 200 |
| E2E | Flujos críticos especificados en 7 archivos Cypress | Las encuestas presenciales SUS/NASA-TLX con 5 operadores quedan como seguimiento post-auditoría; no hay actas ni firmas inventadas |

La suite automatizada cubre flujos de apertura, inventario, ventas, nómina, cierres y dashboard. La presencia de una especificación Cypress no equivale por sí sola a una ejecución aprobada; consulte las evidencias del sprint para el alcance y la fecha de cada ejecución.

## Documentación

- [Especificación de Requisitos (SRS)](docs/SRS.md)
- [Arquitectura de Software (SAD)](docs/SAD.md)
- [Manual de Usuario](docs/manual/MANUAL_USUARIO.md)
- [Evidencias Sprint 1](docs/EVIDENCIAS_SPRINT_1.md) y [Sprint 2](docs/EVIDENCIAS_SPRINT_2.md)
- [SQAP Maestro General v4.0](docs/SQAP-Maestro-General-v4.0.md)
- [Informe histórico de evidencias Sprint 4](docs/EVIDENCIAS_SPRINT_4.md)

## Persistencia en Render

El contenedor crea SQLite como conexión predeterminada. El almacenamiento local efímero de un servicio gratuito puede perder la base al reemplazar/reiniciar el contenedor; para datos operativos persistentes configura una base administrada mediante las conexiones ya disponibles en `config/database.php` o un volumen persistente compatible. Define `APP_KEY` y un `ADMIN_PASSWORD` seguro en las variables de entorno de Render. El servicio responde por HTTPS y `public/hot` se excluye del contexto Docker.
