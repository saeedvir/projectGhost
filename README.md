# Project Change Monitoring
This package will help you see changes to a pre-built Laravel project and get those changes as a compressed file.

Here are a few short examples of what you can do:

##Notice
Note that this package is in development and may have a lot of bugs at first

### How to install ?

```php
composer require saeedvir/projectghost
```
### How to use ?

This command creates a digital signature from all the files in the project
```php
php artisan project:ghost init
```

Now you can work on the project and apply the changes

The following command finds files that have been modified or created or deleted
```php
php artisan project:ghost scan
```

If you use the following command, make these changes in a zip file
```php
php artisan project:ghost scan zip
```

Or the following command will show you a summary of these changes
```php
php artisan project:ghost scan log
```

For Help :
```php
php artisan project:ghost help
```

## Security

If you discover any security related issues, please email [saeed.es91@gmail.com](mailto:saeed.es91@gmail.com) instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
