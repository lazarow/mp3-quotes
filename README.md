# Mp3-Quotes
Generuje pliki Mp3 na podstawie wpisanego tekstu.

Wymaga:
* PHP >= 5.4
* Composer
* SOX + obsługa MP3
* Dostęp do API Ivony

Domyślnie ustawiony na język polski. Konfiguracja sprowadza się do zmiany pliku config.php.
```php
define('IVONA_API_ACCESS_KEY', '');
define('IVONA_API_SECRET_KEY', '');
define('IVONA_API_REGION', '');
define('SOX_PATH', '');
```

Skrypt uruchamiamy przez web server (np. Wamp) otrzymujemy wówczas formatkę do tworzenia audio cytatów.