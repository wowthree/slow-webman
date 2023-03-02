<?php

namespace app\admin\lib\codeGenerator;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Phinx\Util\Util;
use support\Response;

class MigrationGenerator
{
    protected string $stub = __DIR__ . '/stubs/migration.stub';

    protected Collection $columns;

    protected bool $needSoftDelete = false;

    protected bool $needTimestamp = false;

    protected string $primaryKey = '';

    public static function make(): static
    {
        return new static();
    }

    public function generate($table, $columns): string
    {
        $this->columns = $columns;

        $name = 'create_' . $table . '_table';

        return $this->create($name, database_path('migrations'), $table);
    }

    public function create($name, $path, $table)
    {
        // 确认存在
        $className = Str::studly($name);
        if (class_exists($className)) {
            throw new \InvalidArgumentException("A {$className} class already exists.");
        }

        $filePath = $this->getPath($name, $path);

        if (!is_dir(dirname($filePath))) {
            throw new \InvalidArgumentException("the path {$path} dose not exists.");
        }
        // 获取模版
        $stub = file_get_contents($this->stub);
        // 保存
        if (file_put_contents($filePath, $this->populateStub($stub, $table, $className)) === false) {
            throw new \RuntimeException("The file '{$filePath}' could not be written to");
        }

        return $path;
    }

    public function getPath($name, $path)
    {
        return $path . '/' . $this->getDatePrefix() . '_' . $name . '.php';
    }

    public function getDatePrefix(): string
    {
        return Util::getCurrentTimestamp();
    }

    protected function populateStub($stub, $table, $className): array|string
    {
        return str_replace(['{{ content }}', '{{ className }}'], [$this->generateContent($table), $className], $stub);
    }

    public function primary($key): static
    {
        $this->primaryKey = $key;

        return $this;
    }

    public function generateContent($table): string
    {
        empty($this->columns) && abort(400, 'Table fields can\'t be empty');

        $rows   = [];
        if ($this->primaryKey) {
            $rows[] = "\${$table} = \$this->table('{$table}' , ['id'=>'{$this->primaryKey}']);\n";
        } else {
            $rows[] = "\${$table} = \$this->table('{$table}' , ['id'=>false]);\n";
        }
        $rows[] = "\${$table}\n";
        $indexs = '[';
        $uniques = '[';
        foreach ($this->columns as $field) {
            $additional = Arr::get($field, 'additional');
            Arr::get($field, 'index') == 'index' && $indexs .= " '{$field['name']}',"; // 普通索引
            Arr::get($field, 'index') == 'unique' && $uniques .= " '{$field['name']}',"; // 唯一索引

            $options = '[';
            Arr::get($field, 'length') && $options .= " 'length' => {$field['length']},"; // 长度
            Arr::get($field, 'nullable') == '' && $options .= " 'null' => false,"; // 是否可以为空
            Arr::get($field, 'default') != '' && $options .= " 'default' => " . (is_string($field['default']) ? "'{$field['default']}'," : "{$field['default']}"); // 默认值
            Arr::get($field, 'comment') != '' && $options .= " 'comment' => '{$field['comment']}',"; // 注释

            // if ($field['type'] == 'string' && Arr::get($field, 'nullable') == '' && Arr::get($field, 'default') == '') {
            //     $options .= "'default' => '',";
            // }

            if ($additional != '') {
                $options .= $this->parseAdditions($additional);
            }
            $options = strlen($options) > 1 ? ', ' . $options . ']' : '';

            $column = "->addColumn('{$field['name']}' , '{$field['type']}' {$options})";

            $rows[] = $column . "\n";
        }

        if ($this->needSoftDelete) {
            $rows[] = "->addColumn('deleted_at' , 'timestamp')\n";
        }

        if (strlen($indexs) > 1) {
            $indexs = rtrim($indexs, ',');
            $rows[] = "->addIndex({$indexs}])\n";
        }
        if (strlen($uniques) > 1) {
            $uniques = rtrim($uniques, ',');
            $rows[] = "->addIndex({$uniques}] , [ 'unique'=>true ])\n";
        }

        if ($this->needTimestamp) {
            $rows[] = "->addTimestamps()\n";
        }

        $rows[] = "->create();";

        return trim(implode(str_repeat(' ', 8), $rows), "\n");
    }

    /**
     * 处理额外参数
     * @param string $additional 'signed'=false,'values'=['up','down','middle'],...
     * @Auther wow3ter 
     */
    public function parseAdditions($additional): string
    {
        if (!str_contains($additional, '=>')) {
            $additional = str_replace('=', '=>', $additional);
        }

        return $additional;
    }

    public function softDelete(bool $need): static
    {
        $this->needSoftDelete = $need;

        return $this;
    }

    public function timestamps(bool $need): static
    {
        $this->needTimestamp = $need;

        return $this;
    }

    public function stubPath(): string
    {
        return __DIR__ . '/stubs';
    }
}
