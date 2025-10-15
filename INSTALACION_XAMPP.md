# 🚀 INSTALACIÓN CON XAMPP (RECOMENDADA)

## Pasos para configurar OSPOS con XAMPP:

### 1. Descargar e Instalar XAMPP
- Ve a: https://www.apachefriends.org/es/download.html
- Descarga XAMPP para Windows (PHP 8.1 o superior)
- Instala en la ubicación por defecto (C:\xampp)

### 2. Iniciar Servicios
- Abre XAMPP Control Panel
- Inicia **Apache** y **MySQL**
- Verifica que ambos estén en verde (Running)

### 3. Configurar Base de Datos
```bash
# Abrir phpMyAdmin: http://localhost/phpmyadmin
# Crear nueva base de datos: ospos_local
# Importar archivo: app/Database/database.sql
```

### 4. Actualizar Configuración PHP (si es necesario)
Si tu PHP de XAMPP no tiene las extensiones necesarias:
- Editar: C:\xampp\php\php.ini
- Descomentar líneas:
  ```
  extension=mysqli
  extension=pdo_mysql
  extension=intl
  extension=gd
  ```

### 5. Ejecutar OSPOS
```bash
# En la terminal, desde el directorio del proyecto:
C:\xampp\php\php.exe -S localhost:8000 -t public

# O usar el servidor de XAMPP:
# Copiar todo el proyecto a C:\xampp\htdocs\ospos
# Acceder a: http://localhost/ospos
```

### 6. Acceder al Sistema
- URL: http://localhost:8000
- Usuario: admin
- Contraseña: pointofsale

## ✅ Verificación
Una vez instalado, ejecuta:
```bash
php test_connection.php
```