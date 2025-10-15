@echo off
REM ==============================================================================
REM OSPOS Database Backup and Restore Utility
REM ==============================================================================

setlocal enabledelayedexpansion

set DB_PATH=writable\ospos_restaurante.db
set BACKUP_DIR=writable\backups
set TIMESTAMP=%date:~-4%%date:~-7,2%%date:~-10,2%_%time:~0,2%%time:~3,2%%time:~6,2%
set TIMESTAMP=%TIMESTAMP: =0%

echo ==============================================================================
echo OSPOS Database Backup and Restore Utility
echo ==============================================================================
echo.

:MENU
echo Please select an option:
echo.
echo 1. Create backup of current database
echo 2. Restore from backup
echo 3. Apply database fix
echo 4. Verify database
echo 5. Exit
echo.
set /p choice="Enter your choice (1-5): "

if "%choice%"=="1" goto BACKUP
if "%choice%"=="2" goto RESTORE
if "%choice%"=="3" goto APPLY_FIX
if "%choice%"=="4" goto VERIFY
if "%choice%"=="5" goto EXIT
echo Invalid choice. Please try again.
echo.
goto MENU

:BACKUP
echo.
echo Creating backup...
if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"
copy "%DB_PATH%" "%BACKUP_DIR%\ospos_restaurante_%TIMESTAMP%.db"
if errorlevel 1 (
    echo ERROR: Failed to create backup!
) else (
    echo SUCCESS: Backup created at %BACKUP_DIR%\ospos_restaurante_%TIMESTAMP%.db
)
echo.
pause
goto MENU

:RESTORE
echo.
echo Available backups:
echo.
dir /b /o-d "%BACKUP_DIR%\*.db" 2>nul
if errorlevel 1 (
    echo No backups found!
    echo.
    pause
    goto MENU
)
echo.
set /p backup_file="Enter the backup filename to restore (or 'cancel' to go back): "
if /i "%backup_file%"=="cancel" goto MENU

if not exist "%BACKUP_DIR%\%backup_file%" (
    echo ERROR: Backup file not found!
) else (
    echo WARNING: This will overwrite the current database!
    set /p confirm="Are you sure? (yes/no): "
    if /i "!confirm!"=="yes" (
        copy "%BACKUP_DIR%\%backup_file%" "%DB_PATH%"
        if errorlevel 1 (
            echo ERROR: Failed to restore backup!
        ) else (
            echo SUCCESS: Database restored from backup!
        )
    ) else (
        echo Restore cancelled.
    )
)
echo.
pause
goto MENU

:APPLY_FIX
echo.
echo Applying database fix...
echo.
echo Creating backup before applying fix...
if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"
copy "%DB_PATH%" "%BACKUP_DIR%\ospos_restaurante_before_fix_%TIMESTAMP%.db"

echo Applying fix...
sqlite3 "%DB_PATH%" < fix_ospos_database_complete.sql
if errorlevel 1 (
    echo ERROR: Failed to apply fix!
) else (
    echo SUCCESS: Database fix applied successfully!
    echo Backup saved at: %BACKUP_DIR%\ospos_restaurante_before_fix_%TIMESTAMP%.db
)
echo.
pause
goto MENU

:VERIFY
echo.
echo Verifying database...
echo.
php verify_database_fix.php
echo.
pause
goto MENU

:EXIT
echo.
echo Exiting...
exit /b 0
