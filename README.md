Locale URLs
===========

Automatic locale/language management for URLs.

This extension allows to use URLs that contain a language code like

    /en/some/page
    /de/some/page
    http://www.example.com/en/some/page
    http://www.example.com/de/some/page

The language code is automatically inserted into every URL created and
read back on every request. No extra URL rules are required. For best
user experience the language is also restored from session/cookie if the
user returns to a URL without a language code.

# Installation

Extract the package to your `protected/extensions` directory and rename
it to `localeurls`. Then configure the components in your `protected/config/main.php`:

```php
<?php
return array(
    // ...
    'components' => array(
        // ...
        'urlManager' => array(
            'class'     => 'ext.localeurls.LocaleUrlManager',

            // Advanced configuration with defaults (see below)
            //'languageParam'   => 'language',
        ),
        'request' => array(
            'class'     => 'ext.localeurls.LocaleHttpRequest',
            'languages' => array('en_us','en','de','fr'),

            // Advanced configuration with defaults (see below)
            //'persistLanguage'         => true,
            //'languageCookieLifetime'  => 31536000,
            //'redirectDefault'         => false,
        ),
        // ...
    ),
);
```

> **NOTE**: You need to configure all available languages. More specific
> languages should come first, e.g. `en_us` before `en` above.

# Mode of operation

With the above configuration in place you're all set to go. All URLs you create with
any `createUrl()` and `createAbsoluteUrl()` method will contain the current application
language code in their URL.

If a request comes in that has no language set, nothing will happen. The application
will use the language you defined in your `main.php`. If you want a redirect to the
URL with your default language in this case, then configure `redirectDefault` to `true`.

To switch to another language, you can create URLs with the usual methods and add a
`language` parameter there:


```php
<?php
$germanUrl = $this->createUrl('site/contact', array('language' => 'de'));
```

Once a user visited a URL with a language code in it, this language is stored in his
session. If the user returns to the start page (or any other page) without a language
in the URL, he's automatically redirected to his last language choice.

# API

## LocaleHttpRequest

### Properties

 *  `languageCookieLifeTime`: How long to store the user language in a cookie.
    Default is 1 year. Set to false to disable cookie storage.
 *  `languages`: Array of available language codes. More specific patterns must come
    first, i.e. `en_us` must be listed before `en`.
 *  `persistLanguage`: Wether to store the user language selection in session and cookie.
    If the user returns to any page without a language in the URL, he's redirected to the
    stored language's URL. Default is `true`.
 *  `redirectDefault`: Wether to also redirect the application's default language, i.e.
    from `www.example.com` to `www.example.com/en` if the main application language is `en`.
    Default is false.

### Methods

 *  `getDefaultLanguage()`: Returns the default language as it was configured in the main
    application configuration

## LocaleUrlManager

### Properties

 *  `languageParam` : Name of the parameter that contains the desired language when
    constructing a URL. Default is `language`.

### Methods

This class adds no new methods.


# Upgrade

## From 1.0.0

*   The parameter `defaultLanguage` was removed. You should configure the default
    language in your main application config instead. If you want to redirect to
    your default language, you can set `redirectDefault` to true.
