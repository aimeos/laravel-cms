# Laravel CMS

Easy but powerful Laravel CMS package.
It can be installed in any existing Laravel application.

## Installation

Run this command within your Laravel application directory:

```bash
php artisan cms:install
```

## Authorization

To allow existing users to edit CMS content, you can use the `cms:editor` command and replace the e-mail address by the users one:

```bash
php artisan cms:editor me@localhost
```

To disallow users to edit CMS content, use:

```bash
php artisan cms:editor --disable me@localhost
```

## Clean up

To clean up deleted pages, contents and files regularly, add these lines to the `schedule()` method in your `app/Console/Kernel.php` class:

```php
$schedule->command('model:prune', [
    '--model' => [\Aimeos\Cms\Models\Page::class, \Aimeos\Cms\Models\Content::class, \Aimeos\Cms\Models\File::class],
])->daily();
```
