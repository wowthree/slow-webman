<?php

declare(strict_types=1);

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

final class InstallSlowWebman extends AbstractMigration
{

    public function init(): void
    {
        $database = $this->getAdapter()->getOption('name');
        if (!$this->getAdapter()->hasDatabase($database)) {
            $this->createDatabase($database, ['charset' => 'utf8mb4', 'collection' => 'utf8mb4_unicode_ci']);
        }
    }

    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * type   : biginteger, binary, boolean, date, datetime, decimal, float, integer, 
     *        string, text, time, timestamp, uuid, enum, set, blob, json
     * options: length , default, null(允许空), after(放置在哪个字段后), comment, precision与scale,
     *        signed, values(enum), update与delete(外键)
     * 
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $admin_user = $this->table('admin_users');
        $admin_user->addColumn('username', 'string', ['length' => 120, 'null' => false])
            ->addColumn('password', 'string', ['length' => 80, 'null' => false])
            ->addColumn('name', 'string', ['length' => MysqlAdapter::TEXT_TINY, 'null' => false])
            ->addColumn('avatar', 'string', ['length' => MysqlAdapter::TEXT_TINY,])
            ->addColumn('remember_token', 'string', ['length' => 100])
            ->addTimestamps()
            ->addIndex(['username'], ['unique' => true])
            ->create();

        $admin_role = $this->table('admin_roles');
        $admin_role->addColumn('name', 'string', ['length' => 50, 'null' => false])
            ->addColumn('slug', 'string', ['length' => 50, 'null' => false])
            ->addIndex(['name', 'slug'], ['unique' => true])
            ->addTimestamps()
            ->create();

        $admin_permission = $this->table('admin_permissions');
        $admin_permission->addColumn('name', 'string', ['length' => 50, 'null' => false])
            ->addColumn('slug', 'string', ['length' => 50, 'null' => false])
            ->addColumn('http_method', MysqlAdapter::PHINX_TYPE_JSON)
            ->addColumn('http_path', MysqlAdapter::PHINX_TYPE_JSON)
            ->addColumn('order', MysqlAdapter::PHINX_TYPE_INTEGER, ['default' => 0, 'null' => false])
            ->addColumn('parent_id', MysqlAdapter::PHINX_TYPE_INTEGER, ['default' => 0, 'null' => false])
            ->addTimestamps()
            ->addIndex(['name', 'slug'], ['unique' => true])
            ->create();

        $admin_menu = $this->table('admin_menus');
        $admin_menu->addColumn('parent_id', MysqlAdapter::PHINX_TYPE_INTEGER, ['default' => 0, 'null' => false])
            ->addColumn('order', MysqlAdapter::PHINX_TYPE_INTEGER, ['default' => 0, 'null' => false])
            ->addColumn('title', 'string', ['length' => 100, 'comment' => '菜单名称', 'null' => false])
            ->addColumn('icon', 'string', ['length' => 100, 'comment' => '菜单图标'])
            ->addColumn('url', 'string', ['comment' => '菜单路由'])
            ->addColumn('url_type', MysqlAdapter::PHINX_TYPE_TINY_INTEGER, ['default' => 1, 'null' => false, 'comment' => '路由类型(1:路由,2:外链)'])
            ->addColumn('visible', MysqlAdapter::PHINX_TYPE_TINY_INTEGER, ['default' => 1, 'null' => false, 'comment' => '是否可见'])
            ->addColumn('is_home', MysqlAdapter::PHINX_TYPE_TINY_INTEGER, ['default' => 0, 'null' => false, 'comment' => '是否为首页'])
            ->addColumn('extension', 'string', ['comment' => '扩展'])
            ->addTimestamps()
            ->create();

        $admin_role_user = $this->table('admin_role_users', ['id' => false]);
        $admin_role_user->addColumn('role_id', MysqlAdapter::PHINX_TYPE_INTEGER, ['null' => false])
            ->addColumn('user_id', MysqlAdapter::PHINX_TYPE_INTEGER, ['null' => false])
            ->addIndex(['role_id', 'user_id'])
            ->addTimestamps()
            ->create();

        $admin_role_permission = $this->table('admin_role_permissions', ['id' => false]);
        $admin_role_permission->addColumn('role_id', MysqlAdapter::PHINX_TYPE_INTEGER, ['null' => false])
            ->addColumn('permission_id', MysqlAdapter::PHINX_TYPE_INTEGER, ['null' => false])
            ->addIndex(['role_id', 'permission_id'])
            ->addTimestamps()
            ->create();

        $admin_permission_menu = $this->table('admin_permission_menus', ['id' => false]);
        $admin_permission_menu->addColumn('permission_id', MysqlAdapter::PHINX_TYPE_INTEGER, ['null' => false])
            ->addColumn('menu_id', MysqlAdapter::PHINX_TYPE_INTEGER, ['null' => false])
            ->addIndex(['permission_id', 'menu_id'])
            ->addTimestamps()
            ->create();

        $admin_setting = $this->table('admin_settings', ['id' => false]);
        $admin_setting->addColumn('key', 'string', ['null' => false])
            ->addColumn('values', MysqlAdapter::PHINX_TYPE_TEXT, ['null' => false])
            ->addTimestamps()
            ->create();

        $admin_extension = $this->table('admin_extensions');
        $admin_extension->addColumn('name', 'string', ['length' => 100, 'null' => false])
            ->addColumn('is_enabled', MysqlAdapter::PHINX_TYPE_TINY_INTEGER, ['default' => 0, 'null' => false])
            ->addIndex(['name'], ['unique' => true])
            ->addTimestamps()
            ->create();
    }
}
