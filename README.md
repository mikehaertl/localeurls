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
            'languages' => array('en','de','fr'),

            // Advanced configuration with defaults (see below)
            //'persistLanguage'         => true,
            //'languageCookieLifetime'  => 31536000,
            //'defaultLanguage'         => false,
        ),
        // ...
    ),
);
```

# Configuration and use

With the above configuration in place you're set to go. All URLs you create with
any `createUrl()` and `createAbsoluteUrl()` method will contain the current application
language code in their URL. If you want to create a link for a user to switch to
another language you can supply a `language` parameter:


```php
<?php
$germanUrl = $this->createUrl('site/contact', array('language' => 'de'));
```

You can change this parameter name in the `$languageParam` option of `LocaleUrlManager`.

If a request comes in that has no language set, nothing will happen. The application
will use the language you defined in your `main.php`. If you always want to enforce a
redirect e.g. from `http://www.example.com` to `http://www.example.com/de` then
you can set `$defaultLanguage` to `de` or to `true` to redirect to the application
language.

Once a user visited a URL with a language code, this language is stored in his
session. If the user returns to the start page (or any other page) without a language
in the URL, he's automatically redirected to his last language choice.

You can disable this feature if you set `$persistLanguage` to `false` in the `request`
component. If you want to disable the cookie and only use session as storage then you
can set the `$languageCookieLifetime` to `false`.

