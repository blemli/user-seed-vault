# UserSeedVault

[![Latest Version on Packagist](https://img.shields.io/packagist/v/blemli/user-seed-vault.svg?style=flat-square)](https://packagist.org/packages/problemli-gmbh/user-seed-vault)[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/problemli-gmbh/user-seed-vault/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/problemli-gmbh/user-seed-vault/actions?query=workflow%3Arun-tests+branch%3Amain)[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/problemli-gmbh/user-seed-vault/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/problemli-gmbh/user-seed-vault/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)[![Total Downloads](https://img.shields.io/packagist/dt/problemli-gmbh/user-seed-vault.svg?style=flat-square)](https://packagist.org/packages/problemli-gmbh/user-seed-vault)



helps you create an encrypted user-seeder. No more recreating users after database reset.



> [!CAUTION] 
>
> 🤖🧠 This Package was vibe coded 🤖🧠



## Installation

You can install the package via composer:

```bash
composer require blemli/user-seed-vault
```

## Usage

```php
php artisan s
$userSeedVault = new Blemli\UserSeedVault();
echo $userSeedVault->echoPhrase('Hello, Blemli!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Stephan Graf](https://github.com/blemli)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
