<?php

namespace cornernote\helpers;

use Yii;

/**
 * Maintain state of a Return Url
 *
 * Allows the user to have multiple tabs open, each tab will handle its own Return Url passed in via the GET or POST params.
 *
 * @author Brett O'Donnell <cornernote@gmail.com>
 * @copyright 2015 Mr PHP
 * @link https://github.com/cornernote/yii2-return-url
 * @license BSD-3-Clause https://raw.github.com/cornernote/yii2-return-url/master/LICENSE
 */
class ReturnUrl
{

    /**
     * @var string The key used in GET and POST requests for the Return Url.
     */
    public static $requestKey = 'ru';

    /**
     * Get url token from the current page url or submitted data for usage in a link or hidden form element.
     *
     * @usage
     * in views/your_page.php
     * ```
     * echo Html::a('my link', ['test/form', 'ru' => ReturnUrl::getToken()]);
     * echo Html::hiddenInput('ru', ReturnUrl::getToken());
     * ```
     *
     * @param bool $currentPage true to use the current page's URL, false to get from request
     * @return string
     */
    public static function getToken($currentPage = true)
    {
        return $currentPage ? self::urlToToken(Yii::$app->request->url) : self::getRequestToken();
    }

    /**
     * Get the URL where we should redirect to.
     *
     * @usage
     * in YourController::actionYourAction()
     * ```
     * return $this->redirect(ReturnUrl::getUrl());
     * ```
     *
     * @param mixed $altUrl alternative URL to use for redirect if there is no URL
     * @return string
     */
    public static function getUrl($altUrl = null)
    {
        $url = self::tokenToUrl(self::getRequestToken()) || $altUrl;
        return $url ? $url : Yii::$app->homeUrl;
    }

    /**
     * Get the Token from the request data.
     *
     * @return string
     */
    private function getRequestToken()
    {
        $requestKey = self::$requestKey;
        $token = isset($_GET[$requestKey]) && is_scalar($_GET[$requestKey]) ? $_GET[$requestKey] : (isset($_POST[$requestKey]) && is_scalar($_POST[$requestKey]) ? $_POST[$requestKey] : false);
        $token = str_replace(chr(0), '', $token); // strip nul byte
        $token = preg_replace('/\s+/', '', $token); // strip whitespace
        return $token;
    }

    /**
     * Convert a URL to a Token.
     *
     * @param string $input the URL to convert
     * @return string
     */
    public function urlToToken($input)
    {
        $key = uniqid();
        Yii::$app->cache->set(self::$requestKey . '.' . $key, $input);
        return $key;
    }

    /**
     * Convert a Token to a URL.
     *
     * @param string $token the Token to convert
     * @return string
     */
    private function tokenToUrl($token)
    {
        if (!is_scalar($token)) return false;
        return Yii::$app->cache->get(self::$requestKey . '.' . $token);
    }

}
