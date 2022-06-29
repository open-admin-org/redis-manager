Redis manager for open-admin
========================

[![StyleCI](https://styleci.io/repos/508859240/shield?branch=master)](https://styleci.io/repos/508859240)
[![Packagist](https://img.shields.io/github/license/open-admin-org/redis-manager.svg?style=flat-square&color=brightgreen)](https://packagist.org/packages/open-admin-ext/redis-manager)
[![Total Downloads](https://img.shields.io/packagist/dt/open-admin-ext/redis-manager.svg?style=flat-square)](https://packagist.org/packages/open-admin-ext/redis-manager)
[![Pull request welcome](https://img.shields.io/badge/pr-welcome-green.svg?style=flat-square)]()



## Screenshot

![extension-redis-manager](https://user-images.githubusercontent.com/86517067/176555243-be034c34-92b4-4b60-8f32-89dd0432be64.png)


## Installation

```
$ composer require open-admin-ext/redis-manager

$ php artisan admin:import redis-manager
```
Open `http://your-host/admin/redis` in your browser.

## Debugging
When error: `Class "Redis" not found` try changing to predis instead.

in `config/database.php` or .env set REDIS_CLIENT to 'predis';
```php
   'client' => env('REDIS_CLIENT', 'predis'),
```

License
------------
Licensed under [The MIT License (MIT)](LICENSE).
