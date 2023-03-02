<?php

namespace app\admin\controller\devTools;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use support\Request;
use support\DB;
use app\admin\renders\Page;
use app\admin\renders\Form;
use app\admin\renders\Card;
use app\admin\renders\Flex;
use app\admin\renders\Alert;
use Phinx\Console\PhinxApplication;
use app\admin\renders\TextControl;
use app\admin\renders\GroupControl;
use app\admin\renders\TableControl;
use app\admin\renders\SelectControl;
use app\admin\renders\VanillaAction;
use app\admin\renders\CheckboxControl;
use app\admin\renders\FieldSetControl;
use app\admin\controller\AdminController;
use app\admin\renders\CheckboxesControl;
use app\admin\lib\codeGenerator\ModelGenerator;
use app\admin\lib\codeGenerator\ServiceGenerator;
use app\admin\lib\codeGenerator\MigrationGenerator;
use app\admin\lib\codeGenerator\ControllerGenerator;
use app\admin\renders\InputKV;
use app\admin\renders\NumberControl;
use app\admin\renders\TableColumn;
use Phinx\Db\Adapter\MysqlAdapter;
use support\Response;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class CodeGeneratorController extends AdminController
{
    protected string $queryPath = 'dev_tools/code_generator';

    public static array $dataTypeMap = [
        MysqlAdapter::PHINX_TYPE_STRING           =>   MysqlAdapter::PHINX_TYPE_STRING,
        MysqlAdapter::PHINX_TYPE_CHAR             =>   MysqlAdapter::PHINX_TYPE_CHAR,
        MysqlAdapter::PHINX_TYPE_TEXT             =>   MysqlAdapter::PHINX_TYPE_TEXT,
        MysqlAdapter::PHINX_TYPE_TINY_INTEGER     =>   MysqlAdapter::PHINX_TYPE_TINY_INTEGER,
        MysqlAdapter::PHINX_TYPE_INTEGER          =>   MysqlAdapter::PHINX_TYPE_INTEGER,
        MysqlAdapter::PHINX_TYPE_BIG_INTEGER      =>   MysqlAdapter::PHINX_TYPE_BIG_INTEGER,
        MysqlAdapter::PHINX_TYPE_MEDIUM_INTEGER   =>   MysqlAdapter::PHINX_TYPE_MEDIUM_INTEGER,
        MysqlAdapter::PHINX_TYPE_FLOAT            =>   MysqlAdapter::PHINX_TYPE_FLOAT,
        MysqlAdapter::PHINX_TYPE_DOUBLE           =>   MysqlAdapter::PHINX_TYPE_DOUBLE,
        MysqlAdapter::PHINX_TYPE_DECIMAL          =>   MysqlAdapter::PHINX_TYPE_DECIMAL,
        MysqlAdapter::PHINX_TYPE_DATE             =>   MysqlAdapter::PHINX_TYPE_DATE,
        MysqlAdapter::PHINX_TYPE_TIME             =>   MysqlAdapter::PHINX_TYPE_TIME,
        MysqlAdapter::PHINX_TYPE_DATETIME         =>   MysqlAdapter::PHINX_TYPE_DATETIME,
        MysqlAdapter::PHINX_TYPE_TIMESTAMP        =>   MysqlAdapter::PHINX_TYPE_TIMESTAMP,
        MysqlAdapter::PHINX_TYPE_YEAR             =>   MysqlAdapter::PHINX_TYPE_YEAR,
        MysqlAdapter::PHINX_TYPE_ENUM             =>   MysqlAdapter::PHINX_TYPE_ENUM,
        MysqlAdapter::PHINX_TYPE_SET              =>   MysqlAdapter::PHINX_TYPE_SET,
        MysqlAdapter::PHINX_TYPE_JSON             =>   MysqlAdapter::PHINX_TYPE_JSON,
        MysqlAdapter::PHINX_TYPE_BOOLEAN          =>   MysqlAdapter::PHINX_TYPE_BOOLEAN,
        MysqlAdapter::PHINX_TYPE_UUID             =>   MysqlAdapter::PHINX_TYPE_UUID,
        MysqlAdapter::PHINX_TYPE_GEOMETRY         =>   MysqlAdapter::PHINX_TYPE_GEOMETRY,
        MysqlAdapter::PHINX_TYPE_POINT            =>   MysqlAdapter::PHINX_TYPE_POINT,
        MysqlAdapter::PHINX_TYPE_LINESTRING       =>   MysqlAdapter::PHINX_TYPE_LINESTRING,
        MysqlAdapter::PHINX_TYPE_POLYGON          =>   MysqlAdapter::PHINX_TYPE_POLYGON,
        MysqlAdapter::PHINX_TYPE_BINARY           =>   MysqlAdapter::PHINX_TYPE_BINARY,
        MysqlAdapter::PHINX_TYPE_BINARYUUID       =>   MysqlAdapter::PHINX_TYPE_BINARYUUID,
        MysqlAdapter::PHINX_TYPE_VARBINARY        =>   MysqlAdapter::PHINX_TYPE_VARBINARY,
        MysqlAdapter::PHINX_TYPE_BLOB             =>   MysqlAdapter::PHINX_TYPE_BLOB,
        MysqlAdapter::PHINX_TYPE_TINYBLOB         =>   MysqlAdapter::PHINX_TYPE_TINYBLOB,
        MysqlAdapter::PHINX_TYPE_MEDIUMBLOB       =>   MysqlAdapter::PHINX_TYPE_MEDIUMBLOB,
        MysqlAdapter::PHINX_TYPE_LONGBLOB         =>   MysqlAdapter::PHINX_TYPE_LONGBLOB,
        MysqlAdapter::PHINX_TYPE_BIT              =>   MysqlAdapter::PHINX_TYPE_BIT,
        'bigint'                                  =>   MysqlAdapter::PHINX_TYPE_BIG_INTEGER,
        'mediumint'                               =>   MysqlAdapter::PHINX_TYPE_MEDIUM_INTEGER,
        'tinyint'                                 =>   MysqlAdapter::PHINX_TYPE_TINY_INTEGER,
        'int'                                     =>   MysqlAdapter::PHINX_TYPE_INTEGER,
    ];

    public function index(): Response
    {
        $page = Page::make()
            ->title(__('admin.code_generator'))
            ->body($this->form())
            ->remark(
                __('admin.code_generators.remark3')
            );

        return $this->response()->success($page);
    }

    public function form()
    {
        // 下划线的表名处理成驼峰文件名
        $nameHandler =
            'JOIN(ARRAYMAP(SPLIT(IF(ENDSWITH(table_name, "s"), LEFT(table_name, LEN(table_name) - 1), table_name), "_"), item=>CAPITALIZE(item)))';

        return Form::make()
            ->id('code_generator_form')
            ->wrapWithPanel(false)
            ->title(' ')
            ->mode('horizontal')
            ->resetAfterSubmit(true)
            ->api($this->getStorePath())
            ->data([
                'table_info' => $this->getDatabaseColumns(),
            ])
            ->body([
                Card::make()->body(
                    GroupControl::make()->body([
                        GroupControl::make()->direction('vertical')->body([
                            GroupControl::make()->body([
                                TextControl::make()
                                    ->label(__('admin.code_generators.table_name'))
                                    ->name('table_name')
                                    ->value('')
                                    ->required(true),
                                Flex::make()->justify('end')->items([
                                    VanillaAction::make()
                                        ->type('submit')
                                        ->label(__('admin.code_generators.generate_code'))
                                        ->level('primary')
                                        ->icon('fa-solid fa-terminal'),
                                ]),
                            ]),
                            GroupControl::make()->body([
                                TextControl::make()
                                    ->label(__('admin.code_generators.app_title'))
                                    ->name('title')
                                    ->value('${' . $nameHandler . '}'),
                                SelectControl::make()
                                    ->label(__('admin.code_generators.exists_table'))
                                    ->name('exists_table')
                                    ->searchable(true)
                                    ->clearable(true)
                                    ->selectMode('group')
                                    ->options(
                                        $this->getDatabaseColumns()->map(function ($item, $index) {
                                            return [
                                                'label'    => $index,
                                                'children' => $item->keys()->map(function ($item) use ($index) {
                                                    return [
                                                        'value' => $item . '-' . $index,
                                                        'label' => $item,
                                                    ];
                                                }),
                                            ];
                                        })->values()
                                    )
                                    ->onEvent([
                                        'change' => [
                                            'actions' => [
                                                // 更新 table_name 的值
                                                [
                                                    'actionType'  => 'setValue',
                                                    'componentId' => 'code_generator_form',
                                                    'args'        => [
                                                        'value' => [
                                                            'table_name' => '${SPLIT(event.data.value, "-")[0]}',
                                                        ],
                                                    ],
                                                ],
                                                [
                                                    'actionType'  => 'setValue',
                                                    'componentId' => 'code_generator_form',
                                                    'args'        => [
                                                        'value' => [
                                                            'columns' => '${table_info[SPLIT(event.data.value, "-")[1]][SPLIT(event.data.value, "-")[0]]}',
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ]),
                            ]),
                            CheckboxesControl::make()
                                ->name('needs')
                                ->label(__('admin.code_generators.options'))
                                ->joinValues(false)
                                ->extractValue(true)
                                ->checkAll(true)
                                ->defaultCheckAll(true)
                                ->options([
                                    [
                                        'label' => __('admin.code_generators.create_database_migration'),
                                        'value' => 'need_database_migration',
                                    ],
                                    [
                                        'label' => __('admin.code_generators.create_table'),
                                        'value' => 'need_create_table',
                                    ],
                                    [
                                        'label' => __('admin.code_generators.create_model'),
                                        'value' => 'need_model',
                                    ],
                                    [
                                        'label' => __('admin.code_generators.create_controller'),
                                        'value' => 'need_controller',
                                    ],
                                    [
                                        'label' => __('admin.code_generators.create_service'),
                                        'value' => 'need_service',
                                    ],
                                ]),
                            FieldSetControl::make()
                                ->title(__('admin.code_generators.expand_more_settings'))
                                ->collapseTitle(__('admin.code_generators.collapse_settings'))
                                ->collapsable(true)
                                ->collapsed(true)
                                ->titlePosition('bottom')->body([
                                    TextControl::make()
                                        ->label(__('admin.code_generators.primary_key'))
                                        ->name('primary_key')
                                        ->value('id')
                                        ->description(__('admin.code_generators.primary_key_description'))
                                        ->required(true),
                                    TextControl::make()
                                        ->label(__('admin.code_generators.model_name'))
                                        ->name('model_name')
                                        ->value($this->getNamespace('model', 1) . '${' . $nameHandler . '}'),
                                    TextControl::make()
                                        ->label(__('admin.code_generators.controller_name'))
                                        ->name('controller_name')
                                        ->value($this->getNamespace('controller') . '${' . $nameHandler . '}'),
                                    TextControl::make()
                                        ->label(__('admin.code_generators.service_name'))
                                        ->name('service_name')
                                        ->value($this->getNamespace(
                                            'service',
                                            1
                                        ) . '${' . $nameHandler . '}Service'),
                                    CheckboxControl::make()
                                        ->name('need_timestamps')
                                        ->option('CreatedAt & UpdatedAt')
                                        ->value(1),
                                    CheckboxControl::make()
                                        ->name('soft_delete')
                                        ->option(__('admin.soft_delete'))
                                        ->value(0),
                                ]),
                        ]),
                    ]),
                ),

                Card::make()->body([
                    Alert::make()
                        ->body("如果字段名存在 no、status 会导致 form 回显失败! <a href='https://slowlyo.gitee.io/slow-admin-doc/#/docs/issue?id=%f0%9f%90%9b-%e7%bc%96%e8%be%91-%e8%af%a6%e6%83%85%e9%a1%b5%e9%9d%a2%e6%95%b0%e6%8d%ae%e5%9b%9e%e6%98%be%e5%a4%b1%e8%b4%a5' target='_blank'>查看详情</a> 
                        <br>更多额外参数请查看：<a target='_blank' href='https://book.cakephp.org/phinx/0/en/migrations.html#valid-column-options'>Phinx</a>  ")
                        ->level('warning')
                        ->showCloseButton(true)
                        ->showIcon(true),
                    TableControl::make()
                        ->name('columns')
                        ->label(false)
                        ->addable(true)
                        ->needConfirm(false)
                        ->draggable(true)
                        ->removable(true)
                        ->columnsTogglable(false)
                        ->value([
                            [
                                'name'       => '',
                                'type'       => 'string',
                                'additional' => '',
                                'length'     => 255,
                                'index'      => '',
                            ],
                        ])
                        ->columns([
                            TextControl::make()
                                ->name('name')
                                ->width(120)
                                ->label(__('admin.code_generators.column_name'))
                                ->required(true),
                            SelectControl::make()
                                ->name('type')
                                ->label(__('admin.code_generators.type'))
                                ->options($this->availableFieldTypes())
                                ->value('string')
                                ->required(true)
                                ->width(120)
                                ->align('center'),
                            NumberControl::make()
                                ->name('length')
                                ->label(__('admin.code_generators.length'))
                                ->width(60)
                                ->min(0)
                                ->showSteps(false)
                                ->align('center'),
                            CheckboxControl::make()
                                ->name('nullable')
                                ->label(__('admin.code_generators.nullable'))
                                ->width(60)
                                ->align('center'),
                            TextControl::make()
                                ->name('default')
                                ->size('xs')
                                ->width(100)
                                ->label(__('admin.code_generators.default_value'))
                                ->align('center'),
                            TextControl::make()
                                ->name('additional')
                                ->label(__('admin.code_generators.extra_params'))
                                ->placeholder("ex:'after'='name','signed'=true...")
                                ->align('center'),
                            SelectControl::make()
                                ->name('index')
                                ->label(__('admin.code_generators.index'))
                                ->size('xs')
                                ->width(90)
                                ->options(
                                    collect(['index', 'unique'])->map(fn ($value) => [
                                        'label' => $value,
                                        'value' => $value,
                                    ])
                                )
                                ->clearable(true)
                                ->align('center'),
                            TextControl::make()->name('comment')
                                ->label(__('admin.code_generators.comment'))
                                ->align('center'),
                        ]),
                ]),
            ]);
    }

    public function store(Request $request): \support\Response|\JsonSerializable
    {
        $needs   = collect($request->needs);
        $columns = collect($request->columns);

        $paths   = [];
        $message = '';
        try {
            if ($needs->contains('need_model')) {
                $path = ModelGenerator::make()
                    ->primary($request->primary_key)
                    ->timestamps($request->input('need_timestamps', false))
                    ->softDelete($request->input('soft_delete', false))
                    ->generate($request->table_name, $request->model_name);

                $message .= "Model generation succeeded!\n";
                $message .= $path . "\n\n";

                $paths[] = $path;
            }

            if ($needs->contains('need_database_migration')) {
                $path = MigrationGenerator::make()
                    ->primary($request->primary_key)
                    ->timestamps($request->input('need_timestamps', false))
                    ->softDelete($request->input('soft_delete', false))
                    ->generate($request->table_name, $columns);

                $message .= "Migration generation succeeded!\n";
                $message .= $path . "\n\n";

                $paths[] = $path;
            }

            if ($needs->contains('need_controller')) {
                $path = ControllerGenerator::make()
                    ->primary($request->primary_key)
                    ->title($request->title)
                    ->tableName($request->table_name)
                    ->serviceName($request->service_name)
                    ->columns($columns)
                    ->timestamps($request->input('need_timestamps', false))
                    ->generate($request->controller_name);

                $message .= "Controller generation succeeded!\n";
                $message .= $path . "\n\n";

                $paths[] = $path;
            }

            if ($needs->contains('need_service')) {
                $path = ServiceGenerator::make()->generate($request->service_name, $request->model_name);

                $message .= "Service generation succeeded!\n";
                $message .= $path . "\n\n";

                $paths[] = $path;
            }

            if ($needs->contains('need_create_table')) {
                $command = new PhinxApplication();
                $output = new BufferedOutput();
                $command->find('migrate')->run(new ArrayInput(['-e' => 'dev', '-c' => 'phinx.php']), $output);
                $message .= $output->fetch();
            }
        } catch (\Throwable $e) {
            foreach ($paths as $path) {
                unlink($path);
            }

            return $this->response()->fail($e->getMessage());
        }

        return $this->response()->successMessage($message);
    }

    public function availableFieldTypes(): array
    {
        return collect(array_values(self::$dataTypeMap))->map(fn ($value) => ['label' => $value, 'value' => $value])->toArray();
    }

    public function getNamespace($name, $app = null): string
    {
        $namespace = collect(explode('\\', config('admin.route.namespace')));

        $namespace->pop();

        if ($app) {
            $namespace->pop();
        }

        return $namespace->push($name)->implode('/') . '/';
    }

    protected function getDatabaseColumns($db = null, $tb = null)
    {
        $databases = Arr::where(config('database.connections', []), function ($value) {
            $supports = ['mysql'];

            return in_array(strtolower(Arr::get($value, 'driver')), $supports);
        });

        $data = [];

        try {
            foreach ($databases as $connectName => $value) {
                if ($db && $db != $value['database']) {
                    continue;
                }

                $sql = sprintf(
                    'SELECT * FROM information_schema.columns WHERE table_schema = "%s"',
                    $value['database']
                );

                if ($tb) {
                    $p = Arr::get($value, 'prefix');

                    $sql .= " AND TABLE_NAME = '{$p}{$tb}'";
                }

                $sql .= ' ORDER BY `ORDINAL_POSITION` ASC';

                $tmp = Db::connection($connectName)->select($sql);

                $collection = collect($tmp)->map(function ($v) use ($value) {
                    if (!$p = Arr::get($value, 'prefix')) {
                        return (array)$v;
                    }
                    $v = (array)$v;

                    $v['TABLE_NAME'] = Str::replaceFirst($p, '', $v['TABLE_NAME']);

                    return $v;
                });

                $data[$value['database']] = $collection->groupBy('TABLE_NAME')->map(function ($v) {
                    return collect($v)->keyBy('COLUMN_NAME')
                        ->where('COLUMN_NAME', '<>', 'id')
                        ->where('COLUMN_NAME', '<>', 'created_at')
                        ->where('COLUMN_NAME', '<>', 'updated_at')
                        ->where('COLUMN_NAME', '<>', 'deleted_at')
                        ->map(function ($v) {
                            $v['COLUMN_TYPE'] = strtolower($v['COLUMN_TYPE']);
                            $v['DATA_TYPE']   = strtolower($v['DATA_TYPE']);


                            return [
                                'name'     => $v['COLUMN_NAME'],
                                'type'     => Arr::get(self::$dataTypeMap, $v['DATA_TYPE'], 'string'),
                                'default'  => $v['COLUMN_DEFAULT'],
                                'length'   => $v['CHARACTER_MAXIMUM_LENGTH'],
                                'nullable' => $v['IS_NULLABLE'] == 'YES',
                                'comment'  => $v['COLUMN_COMMENT'],
                            ];
                        })->values();
                });
            }
        } catch (\Throwable $e) {
        }

        return collect($data);
    }
}
