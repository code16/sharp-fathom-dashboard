# Sharp Fathom Dashboard

A Laravel package designed to be used with [Sharp](https://github.com/code16/sharp) to display a [Fathom](https://usefathom.com) dashboard.

## Installation

You can install the package via composer:

```bash
composer require code16/sharp-fathom-dashboard
```

You can publish and run the migrations with:

You can publish the config file with:

```bash
php artisan vendor:publish --tag="sharp-fathom-dashboard-config"
```

## Usage

### Setup required environment variables
```dotenv
FATHOM_API_KEY=
FATHOM_SITE_ID=
#Optional, will display a dashboard command to open Fathom
FATHOM_ACCESS_URL= 
```

### Register the Dashboard Sharp's entity in your Sharp Configuration's Service Provider
```php
$config
    ->setName('My Project')
    // ...
    ->declareEntity(Code16\SharpFathomDashboard\Sharp\Entities\FathomDashboardEntity::class);
```

### Add the dashboard in your Sharp's Menu
```php
    return $this
        // ...
        ->addEntityLink(Code16\SharpFathomDashboard\Sharp\Entities\FathomDashboardEntity::class, 'Visits', 'fas-chart-line');
```

Done !


## Credits

- [Lucien PUGET](https://github.com/patrickepatate)
- [All Contributors](../../contributors)
