<?php

namespace BahmanShams;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;

/**
 * Class ShareableLink
 * @package BahmanShams
 */
class ShareableLink
{
    protected $url;
    protected $title;

    public function __construct(string $url, string $title)
    {
        $this->url = $url;
        $this->title = $title;
    }

    /**
     * Build the formatted URL with properly encoded query string parameters.
     *
     * @param string $url
     * @param array $params
     * @return string
     */
    protected function buildFormattedUrl(string $url, array $params): string
    {
        return $url . urldecode(http_build_query($params));
    }

    /**
     * Return the value of a method that is intended for getting a URL string.
     * For example, $this->facebook is the same as $this->getFacebookUrl()
     *
     * @param string $name
     * @return mixed
     * @throws ReflectionException
     */
    public function __get($name)
    {
        $methodName = 'get' . ucfirst($name) . 'Url';

        if (method_exists($this, $methodName)) {
            return $this->{$methodName}();
        }

        $classShortName = (new ReflectionClass($this))->getShortName();
        throw new InvalidArgumentException("Undefined property: {$classShortName}::{$name}");
    }

    public function getFacebookUrl(string $appId = null): string
    {
        $env = function_exists('env') ? 'env' : 'getenv';

        return $this->buildFormattedUrl('https://www.facebook.com/dialog/share?', [
            'app_id' => $appId ?? $env('FACEBOOK_APP_ID') ?: '',
            'href' => $this->url,
            'display' => 'popup',
        ]);
    }

    public function getTwitterUrl(): string
    {
        return $this->buildFormattedUrl('https://twitter.com/intent/tweet?', [
            'url' => $this->url,
            'text' => urlencode($this->limit($this->title, 280)),
        ]);
    }

    public function getWhatsappUrl(): string
    {
        return $this->buildFormattedUrl('https://wa.me/?', [
            'text' => urlencode($this->title . ' ' . $this->url),
        ]);
    }

    public function getLinkedinUrl(): string
    {
        return $this->buildFormattedUrl('https://www.linkedin.com/sharing/share-offsite?', [
            'url' => $this->url,
        ]);
    }

    /**
     * Limit the number of characters in a string.
     *
     * @param string $value
     * @param integer $limit
     * @param string $end
     * @return string
     *
     * @link https://github.com/laravel/framework/blob/5.6/src/Illuminate/Support/Str.php#L212-L227
     */
    protected function limit(string $value, int $limit = 100, string $end = '...'): string
    {
        if (mb_strwidth($value, 'UTF-8') <= $limit) {
            return $value;
        }

        return rtrim(mb_strimwidth($value, 0, $limit, '', 'UTF-8')) . $end;
    }
}
