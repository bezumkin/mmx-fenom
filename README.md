Fenom Template Engine for MODX 3
---

> This extra is part of **MMX** initiative - the **M**odern **M**OD**X** approach.


### Prepare

This package can be installed only with Composer. 

If you are still not using Composer with MODX 3, just download the `composer.json` of your version:
```bash
cd /to/modx/root/
wget https://raw.githubusercontent.com/modxcms/revolution/v3.0.5-pl/composer.json
```

Then run `composer update --no-dev` and you are ready to install the **mmx** packages.

### Install

```bash
composer require mmx/fenom --update-no-dev
composer exec mmx-fenom install
```

### Remove

```bash
composer exec mmx-fenom remove
composer remove mmx/fenom
```

### How to use

You can get and configure the instance of Fenom in any snippet.

For example, snippet `Test`:
```php

$tpl = $modx->getOption('tpl');
$var = $modx->getOption('var');

if ($service = $modx->services->get('mmxFenom')) {
    $service->addModifier('hello', static function($var) {
        return $var . ' World!';
    });
    
    return $service->fetch($tpl, ['var' => $var]);
}

return '';
```

Chunk `Test`:
```html
{$var | hello}
```

And MODX call of snippet with chunk:
```
[[!Test?tpl=`test`&var=`Hello`]]
```

You will get `Hello World!`.

--- 

If you use this package as a dependency for your own extra, you can load and configure the instance inside your class
and make it shared through all snippets to make the same settings and modifiers.

### Template Providers

You have 3 template providers by default:
- MODX Chunk (default, no prefix - just specify id or name)
- MODX Template (template:1, or template:BaseTemplate)
- File (file:name.tpl)

If the MODX element has a static file, it will be used first, without checking the contents of the element in database.

File provider is native for Fenom, it makes no connection to database at all. Use it for maximum Fenom experience.

### System Settings

All settings are prefixed with `mmx-fenom.`.

#### elements-path

The root directory for File provider. 

If it is not existing or not readable, provider will be disabled and you will get INFO record in MODX log.

By default, it is not existing `core/elements` directory.

#### options

JSON encoded string with options to override defaults of Fenom instance. For example:
```json
{"disable_cache":  true}
```

See [Fenom documentation][fenom_docs] for more information.

The default setting are:
```
{
    "disable_cache": false,
    "force_compile": false,
    "auto_reload": true,
    "force_verify": true
}
```

#### use-php

You can enable the potentially **dangerous** use of PHP in templates with `{$.php}` accessor.

It will allow you to do anything with PHP, including deleting source files!

```
Today is {$.php.date('Y-m-d H:i:s')}
```


#### use-modx

You can enable the potentially **dangerous** use of MODX instance in templates with `{$.modx}` accessor.

It will allow you to access to everything in MODX, including deleting resources, elements and directories!

```
Current id of MODX resource is: {$.modx->resource->id}
```

### Modifiers

There are only 3 default modifiers:
- `print` - print escaped variable, `{$var | print}`
- `dump` - dump escaped variable, `{$var | dump}`
- `esc` - escape MODX tags in variable, `{$var | esc}`

### Database Tables

This extra use 2 additional database table to store time of update of MODX chunks and templates, 
as they have no this data by default:
- `mmx_fenom_chunks_time`
- `mmx_fenom_templates_time`

Also, there is additional table for tracking migrations:
- `mmx_fenom_migrations`

### Caching

When caching is enabled, you will get compiled templates in `core/cache/mmx-fenom` directory.

This directory will be deleted when you clear MODX cache.

[fenom_docs]: https://github.com/fenom-template/fenom/blob/master/docs/en/configuration.md#template-settings