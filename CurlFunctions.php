<?php
/**
 * Created by PhpStorm.
 * User: aa
 * Date: 15-Jan-19
 * Time: 11:54 PM
 */

use \GyaaniGuy\Exceptions as Bad;

class CurlFunctions
{

    /**
     * @param $url
     * @param bool $headers
     * @param bool $https
     * @return mixed
     * @throws Bad\CurlFail
     */
    static function fetch($url, $headers = false, $https = true,$post = false, $basicAuth = false)
    {
        $defaultHeaders = ['User-Agent: reddithire app'];
        $ch = curl_init($url);

        if (isset($https) && $https) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }
        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($headers, $defaultHeaders));
        }
        else{
            curl_setopt($ch, CURLOPT_HTTPHEADER, $defaultHeaders);
        }
        if (isset($post) && $post) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        if (isset($basicAuth) && $basicAuth){
            curl_setopt($ch, CURLOPT_HTTPAUTH , CURLAUTH_BASIC  );// authentication method
        curl_setopt($ch, CURLOPT_USERPWD  , GyaaniConf::$clientId.':'.GyaaniConf::$clientSecret);// // authentication
        }

        curl_setopt($ch, CURLOPT_MAXREDIRS, 4);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $curl_scraped_page = curl_exec($ch);

        try {
            self::is_success($ch);
        } catch (Bad\CurlFail $e) {
            GyaaniConf::log($e);
            throw new Bad\CurlFail('curl fetch page fail- ' . $url , Bad\iException::error);
        }
        finally {
            curl_close($ch);
        }

        return $curl_scraped_page;
    }

    /**
     * @param $url
     * @param $saveTo
     * @param $headers
     * @param bool $https
     * @throws Bad\CurlFail
     */
    static function getFile($url, $saveTo, $headers, $https = true)
    {
        $fp = fopen($saveTo, 'w+');
        if ($fp === false) {
            throw new Bad\CurlFail('Not open file for write in curl', BAD\iException::error);
        }
        $ch = curl_init($url);

        if (isset($https) && $https) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }
        if (isset($headers) && $headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        curl_exec($ch);

        try {
            self::is_success($ch);
        } catch (Bad\CurlFail $e) {
            throw $e;
        }
    }

    /**
     * @param $ch
     * @throws Bad\CurlFail
     */
    static function is_success($ch): void
    {
        if (curl_errno($ch) !== 0 ) {
            throw new Bad\CurlFail(curl_error($ch), BAD\iException::warn);
        }
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($statusCode != 200) {
            throw new Bad\CurlFail("Status Code: " . $statusCode, BAD\iException::warn);
        }
    }

}