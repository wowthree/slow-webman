<?php

namespace app\admin\controller;

use Illuminate\Support\Str;
use support\Response;
use support\DB;
use app\admin\renders\Tag;
use app\admin\renders\Page;
use app\admin\renders\Form;
use app\admin\renders\Action;
use app\model\AdminMenu;
use app\admin\renders\TableColumn;
use app\admin\renders\TextControl;
use app\model\AdminPermission;
use app\admin\renders\SelectControl;
use app\admin\renders\NumberControl;
use app\service\AdminMenuService;
use JsonSerializable;
use app\admin\renders\CheckboxesControl;
use app\admin\renders\TreeSelectControl;
use app\service\AdminPermissionService;
use Webman\Route;

class AdminPermissionController extends AdminController
{
    protected string $serviceName = AdminPermissionService::class;

    protected string $queryPath = 'system/admin_permissions';

    protected string $pageTitle;

    public function __construct()
    {
        $this->pageTitle = __('admin.admin_permissions');

        parent::__construct();
    }

    public function index(): Response|JsonSerializable
    {
        if ($this->actionOfGetData()) {
            return $this->response()->success($this->service->list());
        }

        return $this->response()->success($this->list());
    }

    public function list(): Page
    {
        $autoBtn = '';
        if (config('admin.show_auto_generate_permission_button')) {
            $autoBtn = Action::make()
                ->label(__('admin.admin_permission.auto_generate'))
                ->level('success')
                ->confirmText(__('admin.admin_permission.auto_generate_confirm'))
                ->actionType('ajax')
                ->api(admin_url('system/_admin_permissions_auto_generate'));
        }

        $crud = $this->baseCRUD()
            ->loadDataOnce(true)
            ->filterTogglable(false)
            ->footerToolbar([])
            ->headerToolbar([
                $this->createButton(true),
                'bulkActions',
                $autoBtn,
                amis('reload')->align('right'),
                amis('filter-toggler')->align('right'),
            ])
            ->columns([
                TableColumn::make()->label('ID')->name('id')->sortable(true),
                TableColumn::make()->label(__('admin.admin_permission.name'))->name('name'),
                TableColumn::make()->label(__('admin.admin_permission.slug'))->name('slug'),
                TableColumn::make()
                    ->label(__('admin.admin_permission.http_method'))
                    ->name('http_method')
                    ->type('each')
                    ->items(Tag::make()
                        ->label('${item}')
                        ->className('my-1'))
                    ->placeholder(Tag::make()->label('ANY')),
                TableColumn::make()
                    ->label(__('admin.admin_permission.http_path'))
                    ->name('http_path')
                    ->type('each')
                    ->items(Tag::make()
                        ->label('${item}')
                        ->className('my-1')),
                $this->rowActionsOnlyEditAndDelete(true),
            ]);

        return $this->baseList($crud);
    }

    public function form(): Form
    {
        return $this->baseForm()->body([
            TextControl::make()->name('name')->label(__('admin.admin_permission.name'))->required(true),
            TextControl::make()->name('slug')->label(__('admin.admin_permission.slug'))->required(true),
            TreeSelectControl::make()
                ->name('parent_id')
                ->label(__('admin.parent'))
                ->labelField('name')
                ->valueField('id')
                ->value(0)
                ->options($this->service->getTree()),
            CheckboxesControl::make()
                ->name('http_method')
                ->label(__('admin.admin_permission.http_method'))
                ->options($this->getHttpMethods())
                ->description(__('admin.admin_permission.http_method_description'))
                ->joinValues(false)
                ->extractValue(true),
            NumberControl::make()
                ->name('order')
                ->label(__('admin.order'))
                ->required(true)
                ->labelRemark(__('admin.order_desc'))
                ->displayMode('enhance')
                ->min(0)
                ->value(0),
            SelectControl::make()
                ->name('http_path')
                ->label(__('admin.admin_permission.http_path'))
                ->searchable(true)
                ->multiple(true)
                ->options($this->getRoutes())
                ->autoCheckChildren(false)
                ->joinValues(false)
                ->extractValue(true),
            TreeSelectControl::make()
                ->name('menus')
                ->label(__('admin.menus'))
                ->searchable(true)
                ->multiple(true)
                ->showIcon(false)
                ->options(AdminMenuService::make()->getTree())
                ->labelField('title')
                ->valueField('id')
                ->autoCheckChildren(false)
                ->joinValues(false)
                ->extractValue(true),
        ]);
    }

    public function detail($id): Form
    {
        return $this->baseDetail($id)->body([]);
    }

    private function getHttpMethods(): array
    {

        return collect(AdminPermission::$httpMethods)->map(function ($method) {
            return [
                'value' => $method,
                'label' => $method,
            ];
        })->toArray();
    }

    public function getRoutes(): array
    {
        $prefix = (string)config('admin.route.prefix');

        $container = collect();
        return collect(app(Route::class)->getRoutes())->map(function ($route) use ($prefix, $container) {
            if (!Str::startsWith($uri = $route->getPath(), $prefix) && $prefix && $prefix !== '/') {
                return null;
            }
            if (!Str::contains($uri, '{')) {
                if ($prefix !== '/') {
                    $route = Str::replaceFirst($prefix, '', $uri . '*');
                } else {
                    $route = $uri . '*';
                }

                $route !== '*' && $container->push($route);
            }
            $path = preg_replace('/{.*}+/', '*', $uri);
            $prefix !== '/' && $path = Str::replaceFirst($prefix, '', $path);

            return $path;
        })->merge($container)->filter()->unique()->map(function ($method) {
            return [
                'value' => $method,
                'label' => $method,
            ];
        })->values()->all();
    }

    public function autoGenerate()
    {
        $menus = AdminMenu::all()->toArray();

        $permissions = [];
        foreach ($menus as $menu) {
            $_httpPath = $this->getHttpPath($menu['url']);

            $permissions[] = [
                'id'         => $menu['id'],
                'name'       => $menu['title'],
                'slug'       => (string)Str::uuid(),
                'http_path'  => json_encode($_httpPath ? [$_httpPath] : ''),
                'order'      => $menu['order'],
                'parent_id'  => $menu['parent_id'],
                'created_at' => $menu['created_at'],
                'updated_at' => $menu['updated_at'],
            ];
        }

        AdminPermission::query()->truncate();
        AdminPermission::query()->insert($permissions);

        DB::table('admin_permission_menus')->truncate();
        foreach ($permissions as $item) {
            $query = DB::table('admin_permission_menus');
            $query->insert([
                'permission_id' => $item['id'],
                'menu_id'       => $item['id'],
            ]);
            if ($item['parent_id'] != 0) {
                $query->insert([
                    'permission_id' => $item['id'],
                    'menu_id'       => $item['parent_id'],
                ]);
            }
        }

        return $this->response()->successMessage(
            __('admin.successfully_message', ['attribute' => __('admin.admin_permission.auto_generate')])
        );
    }

    private function getHttpPath($uri)
    {
        $excepts = ['/', '', '-'];
        if (in_array($uri, $excepts)) {
            return '';
        }

        if (!str_starts_with($uri, '/')) {
            $uri = '/' . $uri;
        }

        return $uri . '*';
    }
}
