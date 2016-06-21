# ACF Local JSON Manager
**Manages plugins and themes that use ACF Local JSON.**

![](https://dl.dropboxusercontent.com/u/2758854/aljm-screen2.png)


Are you working multiple plugins and themes that utilize [ACF Local JSON](https://www.advancedcustomfields.com/resources/local-json/)?

Since Local JSON files can only save in one place, there is an issue if multiple plugins/themes try to hook on `acf/settings/save_json`.
(Any field groups that you edit will be saved to the last plugin that hooks on the filter.) That's the problem ACF Local JSON Manager 
tries to solve.

With ACF Local JSON Manager you can select which plugin or theme the Local JSON files should be saved to from the comfort of the admin bar.

#### Adding support for ACF Local JSON Manager

You have to add a filter in order to make your plugin/theme compatible with the ACF Local JSON Manager. The snippet looks like this: 

```php
add_filter('aljm_save_json', function($folders) {
  $folders['My plugin'] = dirname(__FILE__) . '/acf';
  return $folders;
});
```

The hook  `aljm_save_json` provides you with a key/value array `$folders` that lists all the folders currently registered in the manager. 
The array key is your plugin or theme name and the value is the path to the folder.

**Adding support to an existing plugin or theme**

If your code currently looks like this:

```php
add_filter('acf/settings/save_json', function() {
    return dirname(__FILE__) . '/acf';
});
```

Simply add the `aljm_save_json` hook underneath it and your code will be compatible with the ACF Local JSON Manager, but it will also
continue to work as before if the manager is not enabled:

```php
//Old hook
add_filter('acf/settings/save_json', function() {
    return dirname(__FILE__) . '/acf';
});

//New added hook
add_filter('aljm_save_json', function($folders) {
  $folders['My plugin'] = dirname(__FILE__) . '/acf';
  return $folders;
});
```

#### Changelog

* 1.2 - [GitHub Updater](https://github.com/afragen/github-updater) support
* 1.1 - Simplified activation mechanism. If you see "Local JSON: _none" in your admin bar please open the submenu and select Disable Overrides to reset it.
* 1.0 - Initial release