# 🚀 GUÍA DE INSTALACIÓN MYSQL PARA OSPOS

## OPCIÓN 1: MySQL Installer (Oficial - Recomendada)

### 1. Descargar MySQL
- Ve a: https://dev.mysql.com/downloads/installer/
- Descarga "MySQL Installer for Windows" (mysql-installer-web-community-8.0.xx.x.msi)
- Ejecuta el instalador

### 2. Configuración de Instalación
- Selecciona "Custom" o "Server only"
- Instala:
  - MySQL Server 8.0
  - MySQL Workbench (opcional, para gestión visual)

### 3. Configurar MySQL Server
- **Puerto:** 3306 (por defecto)
- **Root password:** `rootpass123` (o la que prefieras)
- **Authentication Method:** Legacy (recomendado para compatibilidad)

### 4. Crear Base de Datos
Abre Command Prompt como administrador y ejecuta:
```sql
mysql -u root -p
-- Introduce la contraseña que configuraste

CREATE DATABASE ospos_local CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
CREATE USER 'ospos_user'@'localhost' IDENTIFIED BY 'ospos_pass';
GRANT ALL PRIVILEGES ON ospos_local.* TO 'ospos_user'@'localhost';
FLUSH PRIVILEGES;
exit;
```

### 5. Importar Estructura de Base de Datos
```bash
cd /d D:\pos_ventas\posventa
mysql -u ospos_user -p ospos_local < app/Database/database.sql
```

## OPCIÓN 2: XAMPP (Incluye Apache, MySQL, PHP)

### 1. Descargar XAMPP
- Ve a: https://www.apachefriends.org/download.html
- Descarga la versión para Windows
- Instala en C:\xampp (ubicación por defecto)

### 2. Iniciar Servicios
- Abre XAMPP Control Panel
- Inicia **MySQL** (Apache opcional)

### 3. Configurar Base de Datos
- Abre phpMyAdmin: http://localhost/phpmyadmin
- Usuario: root, Contraseña: (vacía por defecto)
- Crear nueva base de datos: `ospos_local`
- Importar: `app/Database/database.sql`

## ✅ VERIFICAR INSTALACIÓN

Una vez completada cualquiera de las dos opciones, ejecuta:

```bash
php test_connection.php
```

Deberías ver:
```
✅ CONEXIÓN EXITOSA!
✅ Base de datos ya contiene X tablas OSPOS
```

## 🚀 EJECUTAR OSPOS

```bash
cd /d D:\pos_ventas\posventa
php -S localhost:8000 -t public
```

Accede a: http://localhost:8000
- Usuario: `admin`
- Contraseña: `pointofsale`

## ❓ Si tienes problemas...

1. **Error de conexión:** Verifica que MySQL esté ejecutándose
2. **Error de permisos:** Ejecuta Command Prompt como administrador
3. **Puerto ocupado:** Cambia el puerto en .env o usa otro puerto en el servidor PHP
