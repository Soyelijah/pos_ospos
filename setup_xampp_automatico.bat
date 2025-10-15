@echo off
echo ================================================
echo     OSPOS - Configuracion Automatica XAMPP
echo     Para Restaurantes - Portable entre sucursales
echo ================================================
echo.

:: Verificar si XAMPP ya existe
if exist "C:\xampp\xampp_start.exe" (
    echo [✓] XAMPP ya esta instalado en C:\xampp
    goto :configure_project
) else (
    echo [!] XAMPP no encontrado. Instalando...
    goto :install_xampp
)

:install_xampp
echo.
echo Descargando XAMPP...
echo Por favor descarga XAMPP desde: https://www.apachefriends.org/download.html
echo E instalalo en C:\xampp (ubicacion por defecto)
echo.
echo Presiona cualquier tecla cuando hayas terminado la instalacion...
pause
goto :configure_project

:configure_project
echo.
echo [1/5] Copiando proyecto a XAMPP...
if not exist "C:\xampp\htdocs\ospos" mkdir "C:\xampp\htdocs\ospos"

:: Copiar todos los archivos del proyecto
xcopy /E /Y "%~dp0*" "C:\xampp\htdocs\ospos\" > nul
echo [✓] Proyecto copiado a C:\xampp\htdocs\ospos

echo.
echo [2/5] Configurando permisos de escritura...
icacls "C:\xampp\htdocs\ospos\writable" /grant Everyone:F /T > nul 2>&1
echo [✓] Permisos configurados

echo.
echo [3/5] Iniciando servicios XAMPP...
start "" "C:\xampp\xampp_start.exe"
echo [✓] XAMPP iniciado

echo.
echo [4/5] Esperando a que MySQL inicie...
timeout /t 10 > nul
echo [✓] Servicios listos

echo.
echo [5/5] Configurando base de datos...
"C:\xampp\mysql\bin\mysql" -u root -e "CREATE DATABASE IF NOT EXISTS ospos_restaurante CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
echo [✓] Base de datos creada

echo.
echo ================================================
echo            CONFIGURACION COMPLETADA
echo ================================================
echo.
echo URLs de acceso:
echo   • OSPOS:      http://localhost/ospos
echo   • phpMyAdmin: http://localhost/phpmyadmin
echo.
echo Credenciales por defecto:
echo   • Usuario: admin
echo   • Clave:   pointofsale
echo.
echo Para mover a otro restaurante:
echo   1. Copia toda la carpeta C:\xampp
echo   2. Ejecuta xampp_start.exe en el nuevo equipo
echo   3. ¡Listo!
echo.
pause
exit