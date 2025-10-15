# 🎯 CONFIGURACIÓN PROFESIONAL CON XAMPP

## Mi método recomendado para desarrollo portable

### 📁 Estructura que vamos a crear:
```
C:\xampp\htdocs\
└── ospos\              <- Nuestro proyecto aquí
    ├── app\
    ├── public\
    ├── writable\
    ├── .env
    └── ...
```

### 🔧 Ventajas de este método:
1. **Portable**: Puedes copiar toda la carpeta C:\xampp a otro equipo
2. **Completo**: Incluye Apache, MySQL, PHP, phpMyAdmin
3. **Fácil**: Una sola instalación para todo
4. **Familiar**: Interfaz gráfica para gestión

### ⚡ Configuración automática:
- Apache: Puerto 80 (o 8080 si está ocupado)
- MySQL: Puerto 3306
- phpMyAdmin: http://localhost/phpmyadmin
- OSPOS: http://localhost/ospos

### 🛠️ Pasos a seguir:
1. Descargar e instalar XAMPP
2. Configurar base de datos automáticamente
3. Copiar proyecto a htdocs
4. Configurar permisos
5. ¡Listo para usar!