Locale URLs
===========

Automatic locale/language management for URLs.

This extension allows to use URLs that contain a language code like

    /en/some/page
    /de/some/page
    http://www.example.com/en/some/page
    http://www.example.com/de/some/page

Since 1.1.3 you can also configure friendlier language names if you want:

    http://www.example.com/english/some/page
    http://www.example.com/deutsch/some/page

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

            // Since version 1.1.3 you can also map url => language
            // 'languages' => array(
            //      'english'   => 'en',
            //      'deutsch'   => 'de',
            //      'fr',
            //  )

            // Advanced configuration with defaults (see below)
            //'detecLanguage'           => true,
            //'languageCookieLifetime'  => 31536000,
            //'persistLanguage'         => true,
            //'redirectDefault'         => false,
        ),
        // ...
    ),
);
```

> **NOTE**: You need to configure all available languages including the
> default language of your application. More specific language codes should
> come first, e.g. `en_us` before `en` above.

# Mode of operation

With the above configuration in place you're all set to go. If a request comes in
that has no language set, the component will try to auto detect the language from
the HTTP headers and redirect to that URL, for example to `www.example.com/fr`. If
`fr` is the default language of your application (i.e. what you configured in
your `main.php`), it will not redirect - unless you set `redirectDefault`
to `true`.

> **NOTE**: If `redirectDefault` is enabled you can't access `www.example.com` anymore
> because you're always redirected to a specific language URL even for the default
> application language.

All URLs you create with any `createUrl()` and `createAbsoluteUrl()` method will
also contain the current visitor's language in their URL. The same rules apply for
the default language: Unless you set `redirectDefault` to `true`, the URLs you create
will not contain a language code if the visitor uses the default language.

To let your users switch to another language, you can create URLs with the usual methods
and add a `language` parameter there:

```php
<?php
$germanUrl = $this->createUrl('site/contact', array('language' => 'de'));
```

Once a user visited a URL with a language code in it, this language is stored in his
session. If the user returns to the start page (or any other page) without a language
in the URL, he's automatically redirected to his last language choice. See below for
a useful widget that creates a simple language selector.

# API

## LocaleHttpRequest

### Properties

 *  `detectLanguage`: Wether to auto detect the preferred user language from
    the HTTP headers. Default is `true`.
 *  `languageCookieLifeTime`: How long to store the user language in a cookie.
    Default is 1 year. Set to `false` to disable cookie storage.
 *  `languages`: Array of available language codes. More specific patterns must come
    first, i.e. `en_us` must be listed before `en`. The default language from the
    application configuration must also be listed here. Since 1.1.3 you can also map
    URL values to languages: `array('de','fr','english'=>'en')`.
 *  `persistLanguage`: Wether to store the user language selection in session and cookie.
    If the user returns to any page without a language in the URL, he's redirected to the
    stored language's URL. Default is `true`.
 *  `redirectDefault`: Wether to also redirect the application's default language, i.e.
    from `www.example.com` to `www.example.com/en` if the main application language is `en`.
    Default is false.

### Methods

 *  `getDefaultLanguage()`: Returns the default language as it was configured in the main
    application configuration before it was overwritten during language detection

## LocaleUrlManager

### Properties

 *  `languageParam` : Name of the parameter that contains the desired language when
    constructing a URL. Default is `language`.

# How to switch languages

Here's a simple example how you can create a language selector widget. It creates
a dropdown taylored towards the popular Bootstrap framework.

```php
<?php
class LanguageSelector extends CWidget
{
    public function run()
    {
        $app        = Yii::app();
        $route      = $app->controller->route;
        $languages  = $app->request->languages;
        $language   = $app->language;
        $params     = $_GET;

        echo CHtml::link($language. ' <b class="caret"></b>', '#', array(
            'class'         => 'dropdown-toggle',
            'data-toggle'   => 'dropdown'
        ));

        array_unshift($params, $route);

        echo '<ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu">';
        foreach($languages as $lang)
        {
            if($lang===$language)
                continue;

            $params['language'] = $lang;

            echo '<li>'.CHtml::link($lang,$params ).'</li>';
        }
        echo '</ul>';
    }
}
```

# Changelog

### 1.1.5

*   Fix #4: Query parameters lost when switching to default language

### 1.1.4

*   Fix #3: Could not create URL to switch to default language

### 1.1.3

*   Add mapping feature.
*   Add debug output under category `ext.localeurls` (only if `YII_DEBUG` is set)

# Upgrade

### From 1.0.0

*   The parameter `defaultLanguage` was removed. You should configure the default
    language in your main application config instead. If you want to redirect to
    your default language, you can set `redirectDefault` to true.
