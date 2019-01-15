<?php

namespace Dirim\BeginningPackage;

use Illuminate\Support\ServiceProvider;
use Dirim\BeginningPackage\Observers\QueryLoggingObserver;
use Dirim\BeginningPackage\Commands\ConvertLangsToVueTranslateJS;
use Dirim\BeginningPackage\Commands\ControllerCrud\CreateControllerCrud;

class BeginningPackServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $publishes = require(__DIR__.'/config/publishPaths.php');

        $this->publishFiles($publishes);

        if (config('beginningPack.transDevMode')) {
            $this->commands([
                ConvertLangsToVueTranslateJS::class,
            ]);
        }

        $this->queryLogObserversInclude();
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/channels.php',
            'logging.channels'
        );

        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateControllerCrud::class,
            ]);
        }
    }

    private function publishFiles(Array $publishes)
    {
        foreach ($publishes as $pkey => $pval) {
            if (is_array($pval)) {
                foreach ($pval as $underPkey => $underPval) {
                    $this->publishes([
                        $underPkey => $underPval,
                    ], $pkey);
                }
            }
        }
    }

    private function queryLogObserversInclude()
    {
        $observerPath = config('beginningPack.observerPath');
        $observerPath = $observerPath
                            ? base_path($observerPath)
                            : app_path('Modelss');

        if (is_dir($observerPath)) {
            $modelClassNames = $this->listModelClassNamesInFolder(
                $observerPath
            );

            foreach ($modelClassNames as $name) {
                $name::observe(QueryLoggingObserver::class);
            }
        }

        \App\User::observe(QueryLoggingObserver::class);
    }

    protected function listModelClassNamesInFolder($dir)
    {
        $modelClassNames = [];
        $folderFileNames = array_diff(scandir($dir), ['.', '..']);

        foreach ($folderFileNames as $name) {
            if (is_dir($dir.'/'.$name)) {
                $modelClassNames = array_merge(
                    $modelClassNames,
                    $this->listModelClassNamesInFolder($dir.'/'.$name)
                );
            } else {
                $modelDir = str_replace('/', '\\', stristr($dir, 'Models'));
                $modelClassNames[] = "App\\{$modelDir}\\".str_replace('.php', '', $name);
            }
        }

        return $modelClassNames;
    }
}