@echo off
echo ================================================
echo     OSPOS - Corrector de Extensiones PHP
echo     Habilitando extensiones faltantes
echo ================================================
echo.

set PHP_INI="D:\programs\php-8.4.5\php.ini"
set PHP_DIR="D:\programs\php-8.4.5"

echo [1/4] Verificando archivos PHP...
if not exist %PHP_INI% (
    echo ERROR: No se encuentra php.ini en %PHP_INI%
    echo Por favor verifica la ruta de tu instalacion PHP
    pause
    exit /b 1
)

echo [2/4] Creando backup de php.ini...
copy %PHP_INI% "%PHP_INI%.backup" > nul
echo ✅ Backup creado: php.ini.backup

echo [3/4] Habilitando extension intl...

:: Verificar si ya esta habilitada
findstr /C:"extension=intl" %PHP_INI% > nul
if %ERRORLEVEL% EQU 0 (
    echo ⚠️  Extension intl ya esta en php.ini, verificando si esta comentada...
    findstr /C:";extension=intl" %PHP_INI% > nul
    if %ERRORLEVEL% EQU 0 (
        echo 🔧 Descomentando extension=intl...
        powershell -Command "(Get-Content '%PHP_INI%') -replace ';extension=intl', 'extension=intl' | Set-Content '%PHP_INI%'"
    ) else (
        echo ✅ Extension intl ya esta habilitada
    )
) else (
    echo 📝 Agregando extension=intl al php.ini...
    echo extension=intl >> %PHP_INI%
)

echo [4/4] Verificando otras extensiones necesarias para OSPOS...

:: Lista de extensiones requeridas por OSPOS
set "extensions=mysqli pdo_mysql pdo_sqlite gd mbstring curl openssl json"

for %%e in (%extensions%) do (
    echo Verificando %%e...
    findstr /C:"extension=%%e" %PHP_INI% > nul
    if %ERRORLEVEL% NEQ 0 (
        echo   📝 Agregando extension=%%e
        echo extension=%%e >> %PHP_INI%
    ) else (
        findstr /C:";extension=%%e" %PHP_INI% > nul
        if %ERRORLEVEL% EQU 0 (
            echo   🔧 Descomentando extension=%%e
            powershell -Command "(Get-Content '%PHP_INI%') -replace ';extension=%%e', 'extension=%%e' | Set-Content '%PHP_INI%'"
        ) else (
            echo   ✅ %%e ya esta habilitada
        )
    )
)

echo.
echo ================================================
echo            CONFIGURACION COMPLETADA
echo ================================================
echo.
echo ✅ Extensiones PHP configuradas correctamente
echo 📄 Backup guardado como php.ini.backup
echo.
echo 🚀 Reiniciando OSPOS...
echo ================================================
echo.

:: Verificar que las extensiones estan disponibles
echo Verificando extensiones...
php -m | findstr /i "intl mysqli pdo_mysql sqlite" > nul
if %ERRORLEVEL% EQU 0 (
    echo ✅ Extensiones verificadas correctamente
    echo.
    echo Iniciando OSPOS en 3 segundos...
    timeout /t 3 > nul

    :: Iniciar OSPOS
    echo =====================================
    echo    OSPOS - Sistema POS Restaurante
    echo =====================================
    echo.
    echo URL: http://localhost:8000
    echo Usuario: admin
    echo Contraseña: pointofsale
    echo.
    echo Presiona Ctrl+C para detener
    echo =====================================

    php -S localhost:8000 -t public
) else (
    echo ❌ Error: Las extensiones no se cargaron correctamente
    echo Por favor reinicia el equipo y ejecuta este script nuevamente
    pause
)

pause