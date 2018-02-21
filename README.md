# Modules

[![Latest Stable Version](https://poser.pugx.org/torann/modules/v/stable.png)](https://packagist.org/packages/torann/modules) [![Total Downloads](https://poser.pugx.org/torann/modules/downloads.png)](https://packagist.org/packages/torann/modules)

Basic module implantation for Laravel.

- [Modules on Packagist](https://packagist.org/packages/torann/modules)
- [Modules on GitHub](https://github.com/torann/modules)

## Installation

### Composer

From the command line run:

```
$ composer require torann/modules
```

### The Service Provider

Open up `config/app.php` and find the `providers` key.

```php
'providers' => [

    \Torann\Modules\ModulesServiceProvider::class,

]
```

### Publish the configurations

Run this on the command line from the root of your project:

```php
artisan vendor:publish --provider="Torann\Modules\ModulesServiceProvider"
```

The configuration file is stored in `/config/modules.php` file and is documented inline. Please note this step is required, when adding a new module it updates this file with it's settings. Along with the configuration file, the sample stubs files are published to the `/resources/stubs/modules` directory. These are used to generate new modules.

### Integrating

To support database factories and seeders, along with routing you will need to do a few more things:

#### Routing

In `/app/Providers/RouteServiceProvider.php` at the end of `map` function add

```php
modules()->loadRoutes($this->app['router'], 'api');
modules()->loadRoutes($this->app['router'], 'web');
```

#### Database

##### Seeding

In your default seeder `/database/seeds/DatabaseSeeder` at this to the end of `run` method add:

```php
modules()->seed($this);
``` 

##### Factories

In `/database/factories/UserFactory.php` (or `/database/factories/ModelFactory.php`) add at the end of file:

```php
modules()->loadFactories($factory);
```

## Commands

### module:make

This command creates new modules. You can create one module or multiple modules at once.

Example usage:

```php
artisan module:make products orders
```

### module:files

Allow to create files in module that already exists.
 
Example usage:

```php
artisan module:make products camera radio
```

### module:migration

Creates migration file in given module

Example usage:

```php
artisan module:migration products create_products_table
```

You can also use optional `--type` and `--table` options to set table and type of migration in order to create migration with template for given type, for example:

```php
artisan module:migration products create_camera_table --table=cameras --type=create
```

This will create migration that is of type `create` - so in `up` method there will be creating `cameras` table and in `down` method deleting `cameras` table

> If the application supports multi-tenancy, the `--tenant` option can be used to store migration file in the tenant subdirectory of the module migration directory.

### module:cache

Create a module cache file for faster module registration.

Example usage:

```php
artisan module:cache
```

### module:clear

Remove the module cache file.

Example usage:

```php
artisan module:clear
```
