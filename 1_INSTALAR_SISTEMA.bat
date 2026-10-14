@echo off
setlocal
cd /d "%~dp0"
chcp 65001 > nul
title Tío Chu - Instalador y Configurador Inicial
color 0E

echo =====================================================================
echo                DISCOTECA TÍO CHU - INSTALADOR INICIAL
echo =====================================================================
echo.
echo [1/6] Verificando entorno PHP...
set "PHP_BIN="
where php >nul 2>nul
if %ERRORLEVEL% equ 0 set "PHP_BIN=php"
if exist "%~dp0php\php.exe" set "PHP_BIN=%~dp0php\php.exe"

if not defined PHP_BIN (
    echo       - PHP no encontrado. Descargando PHP portable 8.4...
    if not exist "%~dp0php" mkdir "%~dp0php"
    powershell -NoProfile -ExecutionPolicy Bypass -Command "$ProgressPreference='SilentlyContinue'; Invoke-WebRequest -Uri 'https://windows.php.net/downloads/releases/latest/php-8.4-nts-Win32-vs17-x64-latest.zip' -OutFile '%TEMP%\tio-chu-php.zip'; Expand-Archive -Path '%TEMP%\tio-chu-php.zip' -DestinationPath '%~dp0php' -Force; Remove-Item '%TEMP%\tio-chu-php.zip' -Force"
    if not exist "%~dp0php\php.exe" (
        color 0C
        echo [ERROR] No se pudo descargar PHP. Revisa la conexion a Internet.
        echo.
        pause
        exit /b 1
    )
    copy /y "%~dp0php\php.ini-development" "%~dp0php\php.ini" > nul
    powershell -NoProfile -ExecutionPolicy Bypass -Command "$p='%~dp0php\php.ini'; $s=Get-Content $p -Raw; $s=$s -replace ';extension_dir = \"ext\"','extension_dir = \"ext\"'; $s=$s -replace ';extension=curl','extension=curl'; $s=$s -replace ';extension=mbstring','extension=mbstring'; $s=$s -replace ';extension=openssl','extension=openssl'; $s=$s -replace ';extension=pdo_sqlite','extension=pdo_sqlite'; $s=$s -replace ';extension=sqlite3','extension=sqlite3'; $s=$s -replace ';extension=fileinfo','extension=fileinfo'; $s=$s -replace ';extension=zip','extension=zip'; Set-Content -Path $p -Value $s -NoNewline"
    set "PHP_BIN=%~dp0php\php.exe"
)
echo       - PHP listo: %PHP_BIN%

echo [2/6] Configurando archivo de entorno (.env)...
if not exist ".env" (
    copy .env.example .env > nul
    echo       - Archivo .env creado a partir de .env.example.
) else (
    echo       - Archivo .env ya existe.
)

echo [3/6] Verificando dependencias (Composer)...
if not exist "vendor" (
    if not exist "%~dp0composer.phar" (
        echo       - Composer no encontrado. Descargando Composer...
        powershell -NoProfile -ExecutionPolicy Bypass -Command "$ProgressPreference='SilentlyContinue'; Invoke-WebRequest -Uri 'https://getcomposer.org/download/latest-stable/composer.phar' -OutFile '%~dp0composer.phar'"
    )
    if not exist "%~dp0composer.phar" (
        color 0C
        echo [ERROR] No se pudo descargar Composer. Revisa la conexion a Internet.
        echo.
        pause
        exit /b 1
    )
    echo       - Instalando paquetes de Composer...
    "%PHP_BIN%" "%~dp0composer.phar" install --no-interaction --prefer-dist --optimize-autoloader
    if %ERRORLEVEL% neq 0 (
        color 0C
        echo [ERROR] Composer no pudo instalar las dependencias.
        echo.
        pause
        exit /b 1
    )
) else (
    echo       - Carpeta vendor detectada correctamente.
)

echo [4/6] Generando clave de seguridad de la aplicación...
call "%PHP_BIN%" artisan key:generate --force
if %ERRORLEVEL% neq 0 exit /b 1

echo [5/7] Preparando Base de Datos SQLite y Datos Oficiales...
if not exist "database\database.sqlite" (
    type nul > "database\database.sqlite"
    echo       - Archivo database.sqlite creado.
    echo       - Ejecutando migraciones iniciales y cargando datos oficiales...
    call "%PHP_BIN%" artisan migrate:fresh --seed --force
) else (
    echo       - Base de datos existente detectada. Aplicando actualizaciones...
    call "%PHP_BIN%" artisan migrate --force
)
if %ERRORLEVEL% neq 0 exit /b 1

echo [6/7] Verificando soporte para Servidor Remoto (Ngrok)...
set "NGROK_OK=0"
where ngrok >nul 2>nul
if %ERRORLEVEL% equ 0 set "NGROK_OK=1"
if exist "%~dp0ngrok.exe" set "NGROK_OK=1"

if "%NGROK_OK%"=="0" (
    echo       - Descargando Ngrok portable para conexion remota...
    powershell -NoProfile -ExecutionPolicy Bypass -Command "$ProgressPreference='SilentlyContinue'; Invoke-WebRequest -Uri 'https://bin.equinox.io/c/bNyj1mQVY4c/ngrok-v3-stable-windows-amd64.zip' -OutFile '%TEMP%\tio-chu-ngrok.zip'; Expand-Archive -Path '%TEMP%\tio-chu-ngrok.zip' -DestinationPath '%~dp0' -Force; Remove-Item '%TEMP%\tio-chu-ngrok.zip' -Force"
    if exist "%~dp0ngrok.exe" (
        echo       - Ngrok portable listo.
    ) else (
        echo       - (Opcional) No se descargo Ngrok. Se podra descargar al iniciar el servidor.
    )
) else (
    echo       - Componente de servidor remoto listo.
)

echo [7/7] Optimizando enlaces y caché...
call "%PHP_BIN%" artisan storage:link 2>nul
call "%PHP_BIN%" artisan optimize:clear

echo.
color 0A
echo =====================================================================
echo              ¡INSTALACIÓN COMPLETADA EXITOSAMENTE!
echo =====================================================================
echo.
echo Credenciales de acceso predeterminadas:
echo   - Correo: DonLudo@gmail.chu  (o DonLudo@gmail.com)
echo   - Clave:  tiochu123
echo.
echo Para abrir el sistema cada día, haz doble clic en:
echo   "2_INICIAR_TIO_CHU.bat"
echo.
echo =====================================================================
echo Presiona cualquier tecla para cerrar este instalador...
pause > nul
