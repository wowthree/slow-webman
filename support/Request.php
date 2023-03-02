<?php

/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace support;

use Illuminate\Support\Str;

/**
 * Class Request
 * @package support
 */
class Request extends \Webman\Http\Request
{
    protected static array $checkedIps = [];
    protected static array $trustedProxies = [];
    public const HEADER_X_FORWARDED_PROTO = 0b001000;

    public function is(...$patterns)
    {
        $path = $this->decodedPath();

        return collect($patterns)->contains(fn ($pattern) => Str::is($pattern, $path));
    }

    public function decodedPath()
    {
        return rawurldecode($this->path());
    }

    public function root()
    {
        return rtrim($this->getSchemeAndHttpHost(), '/');
    }

    public function getSchemeAndHttpHost(): string
    {
        return $this->getScheme() . '://' . $this->host();
    }

    public function getScheme(): string
    {
        return $this->isSecure() ? 'https' : 'http';
    }

    public function isSecure(): bool
    {
        $https = $this->getHttps('HTTPS');

        return !empty($https) && 'off' !== strtolower($https);
    }

    public function getHttps(string $key, mixed $default = null): mixed
    {
        return \array_key_exists($key, $_SERVER) ? $_SERVER[$key] : $default;
    }


    public function __get($name)
    {
        return $this->input($name);
    }
}
