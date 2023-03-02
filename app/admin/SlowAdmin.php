<?php

namespace app\admin;

use Illuminate\Support\Arr;
use app\admin\lib\Context;
use Shopwwi\WebmanAuth\Facade\Auth;
use app\admin\lib\Composer;
use app\admin\traits\Assets;
use app\admin\extend\Manager;
use app\model\AdminUser;
use app\model\AdminMenu;
use app\admin\lib\JsonResponse;
use app\admin\extend\ServiceProvider;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;
use app\service\AdminMenuService;
use app\service\AdminSettingService;

class SlowAdmin
{
    use Assets;

    public static function make(): static
    {
        return new static();
    }

    public static function response(): JsonResponse
    {
        return new JsonResponse();
    }

    /**
     * 获取系统菜单
     *
     * @return array
     */
    public function getMenus(): array
    {
        $user = static::user();
        if ($user->isAdministrator() || config('admin.auth.enable') === false) {
            $list = AdminMenuService::make()->query()->orderBy('order')->get();
        } else {
            $user->load('roles.permissions.menus');
            $list = $user->roles->pluck('permissions')->flatten()->pluck('menus')->flatten()->unique('id')->sortBy('order');
        }

        return $this->list2Menu($list);
    }

    private function list2Menu($list, $parentId = 0, $parentName = ''): array
    {
        $data = [];
        foreach ($list as $key => $item) {
            if ($item['parent_id'] == $parentId) {
                $isLink = $item['url_type'] == AdminMenu::TYPE_LINK;
                $idStr  = "[{$item['id']}]";
                $_temp  = [
                    'name'      => $parentName ? $parentName . '-' . $idStr : $idStr,
                    'path'      => $isLink ? '/_link' : $item['url'],
                    'component' => 'amis',
                    'is_home'   => $item['is_home'],
                    'meta'      => [
                        'href'         => $isLink ? $item['url'] : '',
                        'title'        => $item['title'],
                        'icon'         => $item['icon'] ?? ' ',
                        'hide'         => $item['visible'] == 0,
                        'order'        => $item['order'],
                        'singleLayout' => $parentId != 0 ? '' : 'basic',
                    ],
                ];

                $children = $this->list2Menu($list, (int)$item['id'], $_temp['name']);

                if (!empty($children)) {
                    $_temp['component']            = $parentId == 0 ? 'basic' : 'multi';
                    $_temp['meta']['singleLayout'] = '';
                    $_temp['children']             = $children;
                }

                $data[] = $_temp;
                array_push($data, ...$this->generateMenus($_temp));
                unset($list[$key]);
            }
        }
        return $data;
    }

    public function generateMenus($item): array
    {
        $url = $item['path'] ?? '';

        if (!$url || array_key_exists('children', $item)) {
            return [];
        }

        $menu = fn ($action, $path) => [
            'name'      => $item['name'] . '-' . $action,
            'path'      => $url . $path,
            'component' => 'amis',
            'meta'      => [
                'title'        => Arr::get($item, 'meta.title') . '-' . __('admin.' . $action),
                'hide'         => true,
                'icon'         => Arr::get($item, 'meta.icon'),
                'singleLayout' => Arr::get($item, 'meta.singleLayout'),
            ],
        ];

        return [
            $menu('create', '/create'),
            $menu('show', '/:id'),
            $menu('edit', '/:id/edit'),
        ];
    }

    public static function guard(): \Shopwwi\WebmanAuth\Auth
    {
        return Auth::guard('admin');
    }

    public static function user(): null|AdminUser
    {
        return static::guard()->user();
    }

    /**
     * @param string|null $name
     *
     * @return Manager|ServiceProvider|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function extension(?string $name = '')
    {
        if ($name) {
            return app(Manager::class)->get($name);
        }

        return app(Manager::class);
    }

    public static function classLoader()
    {
        return Composer::loader();
    }

    /**
     * 上下文管理.
     *
     * @return Context
     */
    public static function context()
    {
        return app(Context::class);
    }

    /**
     * 往分组插入中间件.
     *
     * @param array $mix
     */
    public static function mixMiddlewareGroup(array $mix = [])
    {
        $router = app('router');
        $group  = $router->getMiddlewareGroups()['admin'] ?? [];

        if ($mix) {
            $finalGroup = [];

            foreach ($group as $i => $mid) {
                $next = $i + 1;

                $finalGroup[] = $mid;

                if (!isset($group[$next]) || $group[$next] !== 'admin.permission') {
                    continue;
                }

                $finalGroup = array_merge($finalGroup, $mix);

                $mix = [];
            }

            if ($mix) {
                $finalGroup = array_merge($finalGroup, $mix);
            }

            $group = $finalGroup;
        }

        $router->middlewareGroup('admin', $group);
    }

    /**
     * @return AdminSettingService
     */
    public static function setting()
    {
        return settings();
    }
}
