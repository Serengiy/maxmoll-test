

## Инструкция к установке

1) Копировать файл из .env.example в .env
2) run
```aiignore
composer install
```
3) run
```aiignore
php artisan key:generate
```
4) run (команда создает базу данных и заполняет ее тестовыми данными)
```aiignore
php artisan app:refresh 
```
5) run (команда пробегается по тестам)
```aiignore
php artisan test
```

PS При тестировании из докера в командах заменить `php` на `sail`

