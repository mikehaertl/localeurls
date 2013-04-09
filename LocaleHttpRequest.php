<?php
/**
 * LocaleHttpRequest
 *
 * Detect the application language from URL.
 *
 * This class searches for a language code in the current URL and
 * sets it as application language if found. The available languages
 * have to be configured in $languages.
 *
 * If no language is found in the URL it tries to auto detect the
 * preferred language from the HTTP headers. This can be disabled
 * through the $detectLanguage parameter.
 *
 * By default the found language is stored in the user session and in a
 * cookie. If a user enters any page without a language code in the URL
 * he gets redirected to the same page but in his preferred language.
 *
 * If a new user enters the site and $redirectDefault is true the user
 * is redirected to the URL with the default language of the aplication
 * as configured in the application configuration.
 *
 * @author Michael Härtl <haertl.mike@gmail.com>
 * @version 1.1.5
 */
class LocaleHttpRequest extends CHttpRequest
{
    const LANGUAGE_KEY = '__language';

    /**
     * @var bool wether to automatically detect the preferred language from the browser settings
     */
    public $detectLanguage = true;

    /**
     * @var array list of available language codes. More specific patterns first, e.g. 'en_us', 'en'.
     * This can also contain key/value items of the form "<url_name>"=>"<language", e.g. 'english'=>'en'
     */
    public $languages = array();

    /**
     * @var int language cookie lifetime in seconds. Default is 1 year. Set to false to disable cookie.
     */
    public $languageCookieLifetime = 31536000;

    /**
     * @var bool wether to store language selection in session and (optionally) in cookie
     */
    public $persistLanguage = true;

    /**
     * @var bool wether to redirect to the default language URL if no language specified
     */
    public $redirectDefault = false;

    /**
     * @var string pathInfo with language key removed
     */
    protected $_cleanPathInfo;

    /**
     * @var string language as configured in main application config
     */
    protected $_defaultLanguage;

    /**
     * Save default language
     */
    public function init()
    {
        $this->_defaultLanguage = Yii::app()->language;
        parent::init();
    }

    /**
     * @return string the language code that was configured in the main application configuration
     */
    public function getDefaultLanguage()
    {
        return $this->_defaultLanguage;
    }

    public function getPathInfo()
    {
        if($this->_cleanPathInfo===null)
        {
            $this->_cleanPathInfo = parent::getPathInfo();

            $languages = array();
            foreach($this->languages as $k=>$v)
                $languages[] = is_string($k) ? $k : $v;
            $pattern = implode('|', $languages);

            if(preg_match("#^($pattern)\b(/?)#", $this->_cleanPathInfo, $m)) {

                $this->_cleanPathInfo = strtr($this->_cleanPathInfo, array($m[1].$m[2] => ''));
                $language = $m[1];
                Yii::app()->language = $language;

                YII_DEBUG && Yii::trace("Detected language '$language'",'ext.localeurls');

                if($this->persistLanguage) {
                    Yii::app()->user->setState(self::LANGUAGE_KEY, $language);
                    $cookies = $this->cookies;
                    if($this->languageCookieLifetime) {
                        $cookie = new CHttpCookie(self::LANGUAGE_KEY, $language);
                        $cookie->expire = time() + $this->languageCookieLifetime;
                        $cookies->add(self::LANGUAGE_KEY, $cookie);
                    }
                }

                if(!$this->redirectDefault && $language===$this->getDefaultLanguage()) {
                    $url            = $this->getBaseUrl().'/'.$this->_cleanPathInfo;
                    $queryString    = $this->getQueryString();
                    if(!empty($queryString))
                        $url .= '?'.$queryString;

                    $this->redirect($url);
                }

            } else {

                $language = null;

                if($this->persistLanguage) {
                    $language = Yii::app()->user->getState(self::LANGUAGE_KEY);

                    if($language===null)
                        $language = $this->getCookies()->itemAt(self::LANGUAGE_KEY);
                }

                if($language===null && $this->detectLanguage) {
                    foreach($this->preferredLanguages as $preferred)
                        if(in_array($preferred, $this->languages)) {
                            $language = $preferred;
                            break;
                        }
                }

                if($language===null || $language===$this->_defaultLanguage) {
                    if(!$this->redirectDefault)
                        return $this->_cleanPathInfo;
                    else
                        $language = $this->_defaultLanguage;
                }

                if(($baseUrl = $this->getBaseUrl())==='') {
                    $this->redirect('/'.$language.$this->getRequestUri());
                } else {
                    $this->redirect(strtr($this->getRequestUri(), array($baseUrl => $baseUrl.'/'.$language)));
                }
            }
        }
        return $this->_cleanPathInfo;
    }
}
