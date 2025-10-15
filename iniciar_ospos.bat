@echo off
title OSPOS - Sistema POS para Restaurantes
echo.
echo =====================================
echo    OSPOS - Sistema POS Restaurante
echo =====================================
echo.
echo Iniciando servidor en puerto 8000...
echo.
echo URLs de acceso:
echo   Local:    http://localhost:8000
echo   Red:      http://%COMPUTERNAME%:8000
echo.
echo Usuario: admin
echo Clave:   pointofsale
echo.
echo Presiona Ctrl+C para detener
echo =====================================
echo.

php -S 0.0.0.0:8000 -t public
pause
