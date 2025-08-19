# Tinker For Lambda (bref, vapor, etc)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/datpmwork/sls-tinker.svg?style=flat-square)](https://packagist.org/packages/datpmwork/sls-tinker)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/datpmwork/sls-tinker/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/datpmwork/sls-tinker/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/datpmwork/sls-tinker/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/datpmwork/sls-tinker/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/datpmwork/sls-tinker.svg?style=flat-square)](https://packagist.org/packages/datpmwork/sls-tinker)

**Seamless Local-to-Lambda Tinker Bridge with State Persistence**

`sls-tinker` revolutionizes debugging and development for serverless Laravel applications by creating a transparent bridge between your local Tinker session and remote Lambda execution. Experience the familiar comfort of your local `php artisan tinker` while executing commands directly against your production Lambda environment with full state preservation across commands.

Unlike traditional approaches that require web interfaces or SSH access, this package maintains the native Tinker experience you know and love, while seamlessly forwarding each command to your remote Lambda function and preserving the execution state for subsequent commands.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/sls-tinker.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/sls-tinker)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## 🚀 How It Works

```bash
# Start local tinker that connects to your Lambda
php artisan sls:tinker your-lambda-function-name
```

# All commands execute on Lambda but feel completely local

```php
>>> $user = User::find(1)                    // Executes on Lambda
=> App\Models\User {#1234                    // Result from Lambda
     id: 1,
     name: "John Doe",
     email: "john@example.com",
   }

>>> $user->posts->count()                    // State preserved from previous command
=> 5                                         // $user variable still available

>>> $posts = $user->posts()->latest()->take(3)->get()  // Chaining works perfectly
=> Illuminate\Database\Eloquent\Collection {#5678
     all: [/* 3 posts */]
   }
```

## ✨ Key Features

- 🖥️ **Native Local Experience** - Use your familiar local Tinker interface and shortcuts
- ⚡ **Lambda Execution** - Every command runs on your actual Lambda environment
- 💾 **Stateful Sessions** - Variables and state persist across commands seamlessly
- 🔄 **Automatic State Sync** - Previous command context automatically sent with each request
- 🌐 **Multi-Environment** - Switch between different Lambda deployments (staging, production)
- 🔍 **Full Laravel Integration** - Access models, services, facades - everything works as expected
- 📝 **Command History** - Full history support with up/down arrow navigation
- 🏃‍♂️ **Performance Optimized** - Efficient state serialization and minimal overhead

## Why This Approach?

**Traditional serverless debugging problems:**
- No SSH access to Lambda functions
- Can't run interactive commands in production
- Web-based tools feel foreign and limited
- State doesn't persist between commands
- Complex setup and authentication

**`sls-tinker` Solution:**
- Keep using your local terminal and favorite tools
- Execute commands in the actual production environment
- Seamless state management across command invocations
- Zero learning curve - it's just Tinker
- Simple configuration and authentication

## Installation

You can install the package via composer:

```bash
composer require datpmwork/sls-tinker
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="sls-tinker-config"
```

This is the contents of the published config file:

```php
return [
];
```

## Testing

```bash
./vendor/bin/pest
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [datpmwork](https://github.com/datpmwork)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
