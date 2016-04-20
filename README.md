# ACF Local JSON Manager
Manages plugins that use ACF Local JSON

Working with a lot of different plugins that utilize Local JSON?

Since Local JSON can only save in one place at once, you have the problem with fields group in different plugins.

With ACF Local JSON Manager you can simply select which plugin or theme the Local JSON files should be saved in letting you 
fiddle less with code and work more with your fields!

```php
add_filter('aljm_save_json', function($folders) {
  $folders['My plugin'] = dirname(__FILE__) . '/acf';
  return $folders;
});
```