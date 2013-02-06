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
 * By default the language is also stored to the user session and to a
 * cookie. If a user enters any page without a language code in the URL
 * then he's redirected to his stored language version - if available.
 *
 * If a new user enters the site and $defaultLanguage is true or contains
 * a language code, he's redirected to the application language page (true)
 * or the language specified (string).
 *
 * @author Michael Härtl <haertl.mike@gmail.com>
 * @version 1.0.0-dev
 */
class LocaleHttpRequest extends CHttpRequest
{
    const LANGUAGE_KEY = '__language';

    /**
     * @var array list of available language codes
     */
    public $languages = array();

    /**
     * @var bool wether to store language selection in session and (optionally) in cookie
     */
    public $persistLanguage = true;

    /**
     * @var int language cookie lifetime in seconds. Default is 1 year. Set to false to disable cookie.
     */
    public $languageCookieLifetime = 31536000;

    /**
     * @var mixed if the URL contains no language and this is not false, then the user is redirected
     * either to the application language (true) or to the language specified (string)
     */
    public $defaultLanguage = false;

    /**
     * @var string pathInfo with language key removed
     */
    protected $_cleanPathInfo;

    public function getPathInfo()
    {
        if($this->_cleanPathInfo===null)
        {
            $this->_cleanPathInfo = parent::getPathInfo();
            $pattern = implode('|',$this->languages);

            if(preg_match("#^($pattern)(/?)#", $this->_cleanPathInfo, $m)) {

                $this->_cleanPathInfo = strtr($this->_cleanPathInfo, array($m[1].$m[2] => ''));
                Yii::app()->language = $m[1];

                if($this->persistLanguage) {
                    Yii::app()->user->setState(self::LANGUAGE_KEY, $m[1]);
                    $cookies = $this->cookies;
                    if($this->languageCookieLifetime) {
                        $cookie = new CHttpCookie(self::LANGUAGE_KEY, $m[1]);
                        $cookie->expire = time() + $this->languageCookieLifetime;
                        $cookies->add(self::LANGUAGE_KEY, $cookie);
                    }
                }

            } else {

                $language = Yii::app()->user->getState(self::LANGUAGE_KEY);

                if($language===null)
                    $language = $this->getCookies()->itemAt(self::LANGUAGE_KEY);

                if($language===null) {
                    if(!$this->defaultLanguage)
                        return $this->_cleanPathInfo;
                    else
                        $language = $this->defaultLanguage===true ? Yii::app()->language : $this->defaultLanguage;
                }

                if(!in_array($language, $this->languages))
                    throw new CException("Auto-detected language '$language' is not configured.");

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
