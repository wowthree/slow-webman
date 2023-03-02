<?php


use Phinx\Seed\AbstractSeed;

class SlowWebman extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run(): void
    {
        $admin_user = $this->table('admin_users');
        $admin_user->truncate();
        $users = [
            'username' => 'admin',
            'password' => password_hash('admin', PASSWORD_BCRYPT),
            'name'     => 'Administrator'
        ];
        $admin_user->insert($users)->saveData();

        $admin_role = $this->table('admin_roles');
        $admin_role->truncate();
        $roles = [
            'name' => 'Administrator',
            'slug' => 'administrator',
        ];
        $admin_role->insert($roles)->saveData();

        $admin_role_user = $this->table('admin_role_users');
        $admin_role_user->truncate();
        $user_roles = [
            'role_id' => 1,
            'user_id' => 1,
        ];
        $admin_role_user->insert($user_roles)->saveData();

        $admin_permission = $this->table('admin_permissions');
        $admin_permission->truncate();
        $permissions = [
            [
                'name'      => '首页',
                'slug'      => 'home',
                'http_path' => json_encode(['/home*']),
                "parent_id" => 0,
            ],
            [
                'name'      => '系统',
                'slug'      => 'system',
                'http_path' => json_encode([]),
                "parent_id" => 0,
            ],
            [
                'name'      => '管理员',
                'slug'      => 'admin_users',
                'http_path' => json_encode(["/admin_users*"]),
                "parent_id" => 2,
            ],
            [
                'name'      => '角色',
                'slug'      => 'roles',
                'http_path' => json_encode(["/roles*"]),
                "parent_id" => 2,
            ],
            [
                'name'      => '权限',
                'slug'      => 'permissions',
                'http_path' => json_encode(["/permissions*"]),
                "parent_id" => 2,
            ],
            [
                'name'      => '菜单',
                'slug'      => 'menus',
                'http_path' => json_encode(["/menus*"]),
                "parent_id" => 2,
            ],
            [
                'name'      => '设置',
                'slug'      => 'settings',
                'http_path' => json_encode(["/settings*"]),
                "parent_id" => 2,
            ],
        ];
        $admin_permission->insert($permissions)->saveData();

        $admin_menu = $this->table('admin_menus');
        $admin_menu->truncate();
        $menus = [
            [
                'parent_id' => 0,
                'title'     => 'dashboard',
                'icon'      => 'ph:chart-line-up-fill',
                'url'       => '/dashboard',
            ],
            [
                'parent_id' => 0,
                'title'     => 'admin_system',
                'icon'      => 'material-symbols:settings-outline',
                'url'       => '/system',
            ],
            [
                'parent_id' => 2,
                'title'     => 'admin_users',
                'icon'      => 'ph:user-gear',
                'url'       => '/system/admin_users',
            ],
            [
                'parent_id' => 2,
                'title'     => 'admin_roles',
                'icon'      => 'carbon:user-role',
                'url'       => '/system/admin_roles',
            ],
            [
                'parent_id' => 2,
                'title'     => 'admin_permission',
                'icon'      => 'fluent-mdl2:permissions',
                'url'       => '/system/admin_permissions',
            ],
            [
                'parent_id' => 2,
                'title'     => 'admin_menu',
                'icon'      => 'ant-design:menu-unfold-outlined',
                'url'       => '/system/admin_menus',
            ],
            [
                'parent_id' => 2,
                'title'     => 'admin_setting',
                'icon'      => 'mdi:information-outline',
                'url'       => '/system/settings',
            ],
        ];
        $admin_menu->insert($menus)->saveData();
    }
}
