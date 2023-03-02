<?php

namespace app\middleware;

use app\admin\SlowAdmin;
use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;

class AuthCheck implements MiddlewareInterface
{
    public function process(Request $request, callable $next): Response
    {
        if (!$this->shouldPassThrough($request) && config('plugin.shopwwi.auth.app.enable')) {
            if (is_null(SlowAdmin::guard()->user())) {
                return SlowAdmin::response()->additional(['code' => 401])->fail('请先登陆');
            }
        }
        return $next($request);
    }

    /**
     * @param \support\Request $request
     * @Auther wow3ter 
     */
    protected function shouldPassThrough($request): bool
    {
        $excepts = config('admin.auth.except', []);

        return collect($excepts)
            ->merge([
                'login',
                'logout',
                'no-content',
                '_settings',
                'upload_rich',
                'captcha',
            ])
            ->map(function ($path) {
                $prefix = '/' . trim(config('admin.route.prefix'), '/');

                $prefix = ($prefix === '/') ? '' : $prefix;

                $path = trim($path, '/');

                if (is_null($path) || $path === '') {
                    return $prefix ?: '/';
                }
                return $prefix . '/' . $path;
            })
            ->contains(function ($except) use ($request) {
                return $request->is($except);
            });
    }
}
