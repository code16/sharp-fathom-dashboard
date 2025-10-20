# Sharp Fathom Dashboard

A Laravel package designed to be used with [Sharp](https://github.com/code16/sharp) to display a [Fathom](https://usefathom.com) dashboard.

## Installation

You can install the package via composer:

```bash
composer require code16/sharp-fathom-dashboard
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="sharp-fathom-dashboard-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="sharp-fathom-dashboard-config"
```

This is the contents of the published config file:

```php
return [
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="sharp-fathom-dashboard-views"
```

## Usage

```php
$sharpFathomDashboard = new Code16\SharpFathomDashboard();
echo $sharpFathomDashboard->echoPhrase('Hello, Code16!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Lucien PUGET](https://github.com/code16)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
