# Project Change Monitoring
This package will help you see changes to a pre-built Laravel project and get those changes as a compressed file.

Here are a few short examples of what you can do:
<div lang="fa" dir="rtl">

## توضیحات فارسی

در هنگام توسعه پروژه خود گاهی نیاز است که تغییراتی که از یک لحظه اتفاق می افتد را ببینیم
.

از دیگر استفاده های آن می توان به زمانی اشاره کرد که شما در حال توسعه یک پروژه بر روی هاست اشتراکی هستید ، در آن جا شما دسترسی به گیت یا کامپوزر ندارید
بنابراین می توانید تغییرات را ببینید و در یک فایل فشرده به هاست خود منتقل کنید.

</div>

## Notice
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

## How to execute artisan commands from route or controller in Laravel ?

```php
Route::get('ProjectGhostCommands/{command}', function ($command) {

	/*
		For Example :
		
		http://127.0.0.1/ProjectGhostCommands/init 
		http://127.0.0.1/ProjectGhostCommands/scan 
		http://127.0.0.1/ProjectGhostCommands/scan log 
		http://127.0.0.1/ProjectGhostCommands/scan zip 
	*/

	$command = explode(' ',$command);
	if(!isset($command[1])){
		$command[1] = null;
	}
  
    \Artisan::call('project:ghost',['mode'=>$command[0],'options'=>$command[1]]); 

});
```

## Other Packages

- [Laravel Assets Optimizer](https://github.com/saeedvir/laravel-assets-optimizer)
- [Laravel Mysql Backup](https://github.com/saeedvir/laravel-mysql-backup)

## Security

If you discover any security related issues, please email [saeed.es91@gmail.com](mailto:saeed.es91@gmail.com) instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
