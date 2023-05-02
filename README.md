![Laravell CMS](assets/laravel-cms.svg)

# Laravel CMS - Powerful as Contentful, simple as Wordpress!

The easy, flexible and scalable API-first Laravel CMS package:

* Manage structured content like in Contentful
* Define new content elements in seconds
* Allows nested content elements
* Save, publish and revert drafts
* Assign content to multiple pages
* Extremly fast JSON frontend API
* Versatile GraphQL admin API
* Multi-language support
* Multi-domain routing
* Multi-tenancy capable
* Supports soft-deletes
* Fully Open Source
* Scales from single page with SQLite to millions of pages with DB clusters

It can be installed into any existing Laravel application.

## Table of contents

* [Installation](#installation)
* [Authorization](#authorization)
* [Clean up](#clean-up)
* [Multi-domain](#multi-domain)
* [Multi-tenancy](#multi-tenancy)
* [Custom authorization](#custom-authorization)

## Installation

Run this command within your Laravel application directory:

```bash
php artisan cms:install --seed
```

If you don't want to add any demo pages, remove the `--seed` option.

### Authorization

To allow existing users to edit CMS content or to create a new users if they don't exist yet, you can use the `cms:user` command (replace the e-mail address by the users one):

```bash
php artisan cms:user editor@example.com
```

To disallow users to edit CMS content, use:

```bash
php artisan cms:user --disable editor@example.com
```

### Clean up

To clean up soft-deleted pages, contents and files regularly, add these lines to the `schedule()` method in your `app/Console/Kernel.php` class:

```php
$schedule->command('model:prune', [
    '--model' => [
        \Aimeos\Cms\Models\Page::class,
        \Aimeos\Cms\Models\Version::class,
        \Aimeos\Cms\Models\Content::class,
        \Aimeos\Cms\Models\File::class],
])->daily();
```

You can configure the timeframe after soft-deleted items will be removed permantently by setting the [cms.purge](https://github.com/aimeos/laravel-cms/blob/master/config/cms.php#L48) option. It's value must be the number of days after the items will be removed permanently or FALSE if the soft-deleted items shouldn't be removed at all.

### Multi-domain

Using multiple page trees with different domains is possible by changing the `cms.page` route in your `./routes/web.php` to:

```php
Route::group(['domain' => '{domain}'], function() {
    Route::get('{slug?}/{lang?}', [\Aimeos\Cms\Http\Controllers\PageController::class, 'index'])->name('cms.page');
});
```

The `domain` property in the pages must then match the request domain.

### Multi-tenancy

Laravel CMS supports single database multi-tenancy using existing Laravel tenancy packages or code implemented by your own.

The [Tenancy for Laravel](https://tenancyforlaravel.com/) package is most often used. How to set up the package is described in the [tenancy quickstart](https://tenancyforlaravel.com/docs/v3/quickstart) and take a look into the [single database tenancy](https://tenancyforlaravel.com/docs/v3/single-database-tenancy) article too.

Afterwards, tell Laravel CMS how the ID of the current tenant can be retrieved. Add this code to the `boot()` method of your `\App\Providers\AppServiceProvider` in the `./app/Providers/AppServiceProvider.php` file:

```php
\Aimeos\Cms\Tenancy::$callback = function() {
    return tenancy()->initialized ? tenant()->getTenantKey() : '';
};
```

### Custom authorization

If you want to integrate Laravel CMS into another application, you may want to grant access based ony your own authorization scheme. You can replace the Laravel CMS permission handling by adding your own function. Add this code to the `boot()` method of your `\App\Providers\AppServiceProvider` in the `./app/Providers/AppServiceProvider.php` file:

```php
\Aimeos\Cms\Permission::$callback = function( string $action, ?\App\Models\User $user ) : bool {
    if( /* check access */ ) {
        return true;
    }

    return false;
};
```

The first parameter is the action access is requested for, e.g. "page:view" while the second parameter is the authorization bitmap of the user records from the `cmseditor` column of the Laravel `users` table. The function must return TRUE to grant access or FALSE if access is denied.

Available actions which access can be granted to are:

* page:view (show page tree)
* page:save (update existing pages)
* page:add (add new pages)
* page:drop (soft-delete pages)
* page:keep (restore soft-deleted pages)
* page:purge (delete pages permanently)
* page:publish (publish page meta data)
* page:move (move pages in the tree)
* content:view (show content elements)
* content:save (update existing content elements)
* content:add (add new content elements)
* content:drop (soft-delete content elements)
* content:keep (restore soft-deleted content elements)
* content:purge (delete content elements permanently)
* content:publish (publish content elements)
* content:move (move content elements within a page)
* file:view (show uploaded files)
* file:save (update existing files)
* file:add (add new files)
* file:drop (soft-delete files)
* file:keep (restore soft-deleted files)
* file:purge (delete files permanently)
