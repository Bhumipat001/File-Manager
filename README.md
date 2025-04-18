# PHP File Manager

A lightweight, single file PHP based file manager with authentication. Easily upload, delete, and manage files or folders directly from your browser.

## 🔐 Setup

To use this file manager, edit the following lines in `file-manager.php` and set your desired credentials:

```php
define('USERNAME', 'your_username');
define('PASSWORD', 'your_password');
```

Make sure file uploads are enabled in your `php.ini`
```ini
file_uploads = On
```