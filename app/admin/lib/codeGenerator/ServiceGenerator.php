<?php

namespace app\admin\lib\codeGenerator;

use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ServiceGenerator extends BaseGenerator
{
    protected string $stub = __DIR__ . '/stubs/service.stub';

    public function generate($serviceName, $modelName): bool|string
    {
        $name      = str_replace('/', '\\', $serviceName);
        $modelName = str_replace('/', '\\', $modelName);
        $path      = static::guessClassFileName($name);
        $dir       = dirname($path);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (file_exists($path)) {
            abort(400, "Service [$name] already exists!");
        }

        $stub = file_get_contents($this->stub);

        $stub = $this->replaceClass($stub, $name)
            ->replaceModel($stub, $modelName)
            ->replaceNamespace($stub, $name)
            ->replaceSpace($stub);

        file_put_contents($path, $stub);
        chmod($path, 0777);

        return $path;
    }

    public function replaceModel(&$stub, $name): static
    {
        $class = str_replace($this->getNamespace($name) . '\\', '', $name);

        $stub = str_replace(['{{ ModelName }}', '{{ UseModel }}'], [$class, $name], $stub);

        return $this;
    }
}
