<?php

namespace app\admin\lib\codeGenerator;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use support\Response;

class ControllerGenerator extends BaseGenerator
{
    protected string $stub = __DIR__ . '/stubs/controller.stub';

    protected string $serviceName = '';

    protected string $tableName = '';

    protected string $title = '';

    protected Collection $columns;

    protected bool $needTimestamp = false;

    public function timestamps(bool $need): static
    {
        $this->needTimestamp = $need;

        return $this;
    }

    public function serviceName($serviceName): static
    {
        $this->serviceName = $serviceName;

        return $this;
    }

    public function tableName($tableName): static
    {
        $this->tableName = $tableName;

        return $this;
    }

    public function title($title): static
    {
        $this->title = $title;

        return $this;
    }

    public function columns($columns): static
    {
        $this->columns = $columns;

        if ($this->columns->isEmpty()) {
            abort(400, 'Table fields can\'t be empty');
        }

        return $this;
    }

    public function generate($name): bool|string
    {
        $name = str_replace('/', '\\', $name);
        $path = static::guessClassFileName($name);
        $dir  = dirname($path);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (file_exists($path)) {
            abort(400, "Controller [$name] already exists!");
        }

        $stub = file_get_contents($this->stub);

        $stub = $this->replaceClass($stub, $name)
            ->replaceNamespace($stub, $name)
            ->replaceService($stub)
            ->replaceQueryPath($stub)
            ->replaceTitle($stub)
            ->replaceListContent($stub)
            ->replaceFormContent($stub)
            ->replaceDetailContent($stub)
            ->replaceSpace($stub);

        file_put_contents($path, $stub);
        chmod($path, 0777);

        return $path;
    }

    protected function replaceService(&$stub): static
    {
        $name = str_replace('/', '\\', $this->serviceName);

        $class = str_replace($this->getNamespace($name) . '\\', '', $name);

        $stub = str_replace(['{{ ServiceName }}', '{{ UseService }}'], [$class, $name], $stub);

        return $this;
    }

    protected function replaceQueryPath(&$stub): static
    {
        $stub = str_replace('{{ QueryPath }}', Str::snake($this->tableName), $stub);

        return $this;
    }

    protected function replaceTitle(&$stub): static
    {
        $title = $this->title ?? Str::studly($this->tableName);

        $stub = str_replace('{{ PageTitle }}', $title, $stub);

        return $this;
    }

    protected function replaceListContent(&$stub): static
    {
        $list = collect();

        $this->columns->map(function ($column) use (&$list) {
            $item = '';

            $label = Arr::get($column, 'comment') ?? Str::studly($column['name']);

            $item .= "TableColumn::make()->name('{$column['name']}')->label('{$label}')";

            if ($column['type'] == 'integer') {
                $item .= '->sortable(true)';
            }

            $list->push($item);
        });

        if ($this->needTimestamp) {
            $list->push("TableColumn::make()->name('created_at')->label('创建时间')->type('datetime')->sortable(true)");
            $list->push("TableColumn::make()->name('updated_at')->label('更新时间')->type('datetime')->sortable(true)");
        }

        $list = $list->implode(",\n\t\t\t\t") . ',';

        $stub = str_replace('{{ ListContent }}', $list, $stub);

        return $this;
    }

    protected function replaceFormContent(&$stub): static
    {
        $form = collect();

        $this->columns->where('index', '!=', 'primary')->map(function ($column) use (&$form) {
            $item = '';

            $label = Arr::get($column, 'comment') ?? Str::studly($column['name']);

            $item .= "TextControl::make()->name('{$column['name']}')->label('{$label}')";

            $form->push($item);
        });

        $form = $form->implode(",\n\t\t\t") . ',';

        $stub = str_replace('{{ FormContent }}', $form, $stub);

        return $this;
    }

    protected function replaceDetailContent(&$stub): static
    {
        $detail = collect();

        $detail->push("TextControl::make()->static(true)->name('id')->label('ID')");

        $this->columns->map(function ($column) use (&$detail) {
            $item = '';

            $label = Arr::get($column, 'comment') ?? Str::studly($column['name']);

            $item .= "TextControl::make()->static(true)->name('{$column['name']}')->label('{$label}')";

            $detail->push($item);
        });

        if ($this->needTimestamp) {
            $detail->push("TextControl::make()->static(true)->name('created_at')->label('创建时间')");
            $detail->push("TextControl::make()->static(true)->name('updated_at')->label('更新时间')");
        }

        $detail = $detail->implode(",\n\t\t\t");

        $stub = str_replace('{{ DetailContent }}', $detail, $stub);

        return $this;
    }
}
