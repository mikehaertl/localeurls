<?php
/**
 * LocaleUrlManager
 *
 * Add a language code to every created URL in this application.
 *
 * This class is a drop-in replacement for the urlManager application component.
 * It adds the current application language to every created URL:
 *
 *      /en/some/page
 *      /de/some/page
 *      http://www.example.com/en/some/page
 *      http://www.example.com/de/some/page
 *
 * It also works if the application is installed in a subdirectory:
 *
 *      /baseurl/en/some/page
 *      /baseurl/de/some/page
 *      http://www.example.com/baseurl/en/some/page
 *      http://www.example.com/baseurl/de/some/page
 *
 * To create URLs for the user to switch to another language, you can supply a
 * 'language' parameter in your call to any createUrl()/createAbsoluteUrl() method:
 *
 *      $germanUrl = $this->createUrl('site/index', array('language'=>'de'));
 *
 * You have to configure LanguageHttpRequest to parse the above URLs.
 *
 * NOTE: This class only works if urlFormat is 'path'.
 *
 * @author Michael Härtl <haertl.mike@gmail.com>
 * @version 1.1.5
 */
class LocaleUrlManager extends CUrlManager
{
    /**
     * @var string if a URL is constructed with this parameter it overrides the current application language
     */
    public $languageParam = 'language';

    public function init()
    {
        if($this->getUrlFormat()!==self::PATH_FORMAT)
            throw new CException("LanguageUrlManager only works with urlFormat 'path'");

        return parent::init();
    }

    public function createUrl($route,$params=array(),$ampersand='&')
    {
        if(isset($params[$this->languageParam])) {
            $language       = $params[$this->languageParam];
            $forceLanguage  = true;
            unset($params[$this->languageParam]);
        } else {
            $language       = Yii::app()->language;
            $forceLanguage  = false;
        }

        $url = parent::createUrl($route, $params, $ampersand);

        $request = Yii::app()->request;

        if(!$forceLanguage && !$request->redirectDefault && $language===$request->getDefaultLanguage())
            return $url;

        $key = array_search($language, $request->languages);
        if(is_string($key))
            $language = $key;

        if(($baseUrl=$this->getBaseUrl())==='') {
            return '/'.$language.$url;
        } else {
            return strtr($url, array($baseUrl => $baseUrl.'/'.$language));
        }
    }
}
