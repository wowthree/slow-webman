<?php

namespace app\admin\lib\codeGenerator;

use Illuminate\Support\Str;
use support\Response as HttpResponse;

class ModelGenerator extends BaseGenerator
{
    protected bool $needSoftDelete = false;

    protected bool $needTimestamp = false;

    protected string $stub = __DIR__ . '/stubs/model.stub';

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

    public function generate($table, $name): bool|string
    {
        $name = str_replace('/', '\\', $name);
        $path = static::guessClassFileName($name);
        $dir  = dirname($path);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (file_exists($path)) {
            abort(400, "Model [$name] already exists!");
        }

        $stub = file_get_contents($this->stub);

        $stub = $this->replaceClass($stub, $name)
            ->replaceNamespace($stub, $name)
            ->replaceSoftDeletes($stub)
            ->replaceTable($stub, $table, $name)
            ->replaceTimestamp($stub)
            ->replacePrimaryKey($stub)
            ->replaceSpace($stub);

        file_put_contents($path, $stub);
        chmod($path, 0777);

        return $path;
    }

    protected function replaceSoftDeletes(&$stub): static
    {
        $import = $use = '';

        if ($this->needSoftDelete) {
            $import = 'use Illuminate\\Database\\Eloquent\\SoftDeletes;';
            $use    = 'use SoftDeletes;';
        }

        $stub = str_replace(['{{ ImportSoftDelete }}', '{{ SoftDelete }}'], [$import, $use], $stub);

        return $this;
    }

    protected function replaceTable(&$stub, $table, $name): static
    {
        $class = str_replace($this->getNamespace($name) . '\\', '', $name);

        $tableName = Str::plural(strtolower($class)) !== $table ? "protected \$table = '$table';\n" : '';

        $stub = str_replace('{{ ModelTable }}', $tableName, $stub);

        return $this;
    }

    protected function replaceTimestamp(&$stub): static
    {
        $useTimestamps = $this->needTimestamp ? '' : "public \$timestamps = false;\n";

        $stub = str_replace('{{ Timestamp }}', $useTimestamps, $stub);

        return $this;
    }

    protected function replacePrimaryKey(&$stub): static
    {
        $modelKey = $this->primaryKey == 'id' ? '' : "protected \$primaryKey = '{$this->primaryKey}';\n";

        $stub = str_replace('{{ ModelKey }}', $modelKey, $stub);

        return $this;
    }
}
