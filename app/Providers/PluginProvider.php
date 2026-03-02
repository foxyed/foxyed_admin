<?php

namespace App\Providers;

use App\Attributes\IsGranted;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Routing\Attribute\Route as SymfonyRoute;

class PluginProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $pluginPath = base_path('plugins');
        foreach (File::directories($pluginPath) as $pluginDir) {
            $name = explode(DIRECTORY_SEPARATOR, $pluginDir);
            $name = strtolower($name[count($name) - 1]);
            $controllersDir = $pluginDir . DIRECTORY_SEPARATOR . 'Controllers';
            if (File::exists($controllersDir)) {
                $this->loadControllers($controllersDir, $name);
            }
            $apiDir = $pluginDir . DIRECTORY_SEPARATOR . 'ApiControllers';
            if (File::exists($apiDir)) {
                $this->loadControllers($apiDir, $name, true);
            }
        }
    }

    private function loadControllers(string $directory, $name, bool $isApi = false)
    {
        $namespace = $this->getNamespaceFromPath($directory);
        $routePrefix = "";
        Route::prefix($routePrefix)
            ->middleware($isApi ? ['api'] : ['web'])
            ->group(function () use ($isApi, $directory, $namespace, $name) {
                foreach (File::allFiles($directory) as $file) {
                    $class = $namespace . '\\' . $file->getFilenameWithoutExtension();
                    if (!class_exists($class)) continue;
                    $reflection = new \ReflectionClass($class);
                    $classMiddlewares = [];
                    foreach ($reflection->getAttributes(SymfonyRoute::class) as $attribute) {
                        $attribute = $attribute->newInstance();
                        $routePrefix = $attribute->getPath();
                    }

                    foreach ($reflection->getAttributes(IsGranted::class) as $attribute) {
                        $granted = $attribute->newInstance();
                        $classMiddlewares[] = $granted->toMiddleware();

                    }

                    foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                        $routes = $method->getAttributes(SymfonyRoute::class);
                        if (empty($routes)) continue;

                        foreach ($routes as $attribute) {
                            $route = $attribute->newInstance();
                            $methodMiddlewares = [];

                            foreach ($method->getAttributes(IsGranted::class) as $attr) {
                                $grant = $attr->newInstance();
                                $methodMiddlewares[] = $grant->toMiddleware();

                            }

                            $uri = ltrim($route->getPath(), '/');
                            $methods = $route->getMethods() ?: ['GET'];
                            if (empty($routePrefix)) {
                                $routePrefix = "/";
                            }
                            if(!empty($route->getName())){
                                $routeName = $route->getName();
                            } else  {
                                $routeName = $name . "." . $method->getName();
                            }

                            Route::match($methods, "{$routePrefix}/{$uri}", [$class, $method->getName()])
                                ->middleware([...$classMiddlewares, ...$methodMiddlewares])->name($routeName);
                        }
                    }
                }
            });
    }


    protected function getNamespaceFromPath(string $path): string
    {
        $relative = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $path);
        return str_replace('/', '\\', ucwords($relative, '/\\'));
    }
}
