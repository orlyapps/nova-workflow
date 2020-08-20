<?php

namespace Orlyapps\NovaWorkflow;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Nova;
use Orlyapps\NovaWorkflow\Models\WorkflowDefinition;
use Orlyapps\NovaWorkflow\Models\WorkflowRegistry;
use Symfony\Component\Finder\Finder;

class NovaWorkflowServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'nova-workflow');

        $this->app->booted(function () {
            $this->routes();
        });

        Nova::serving(function (ServingNova $event) {
            Nova::script('nova-workflow', __DIR__ . '/../dist/js/app.js');
            Nova::style('nova-workflow', __DIR__ . '/../dist/css/app.css');
        });

        // Regiter migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Publish your config
        $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('workflow.php'),
            ], 'config');

        $this->macros();
    }

    public function macros()
    {
        Field::macro('exceptOnFormsWithStatus', function (array $status = []) {
            return $this->resolveUsing(function ($value, $resource) use ($status) {
                if (in_array($resource->status, $status)) {
                    $this->exceptOnForms();
                }
                return $value;
            });
        });

        Field::macro('onlyOnFormsWithStatus', function (array $status = []) {
            return $this->resolveUsing(function ($value, $resource) use ($status) {
                if (in_array($resource->status, $status)) {
                    $this->onlyOnForms();
                } else {
                    $this->exceptOnForms();
                }
                return $value;
            });
        });
        Field::macro('hideWithStatus', function (array $status = []) {
            return $this->resolveUsing(function ($value, $resource) use ($status) {
                if (in_array($resource->status, $status)) {
                    $this->showOnIndex = false;
                    $this->showOnDetail = false;
                    $this->showOnCreation = false;
                    $this->showOnUpdate = false;
                }
                return $value;
            });
        });
    }

    /**
     * Register the card's routes.
     *
     * @return void
     */
    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova'])
                ->namespace('Orlyapps\NovaWorkflow\Http\Controllers')
                ->prefix('nova-vendor/nova-workflow')
                ->group(__DIR__ . '/../routes/api.php');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'workflow');

        $this->app->singleton('workflow', function ($app) {
            $registry = new WorkflowRegistry();
            foreach ($this->workflows() as $workflow) {
                $registry->add($workflow);
            }
            return $registry;
        });
        app('workflow');
    }

    /**
     * Register the application's workflows.
     *
     * @return void
     */
    protected function workflows()
    {
        $namespace = app()->getNamespace();
        $workflows = [];
        if (!File::isDirectory(app_path('Nova/Workflows'))) {
            File::makeDirectory(app_path('Nova/Workflows'), 0777, true, true);
        }

        foreach ((new Finder)->in(app_path('Nova/Workflows'))->files() as $workflow) {
            $workflow = $namespace . str_replace(
                ['/', '.php'],
                ['\\', ''],
                Str::after($workflow->getPathname(), app_path() . DIRECTORY_SEPARATOR)
            );

            if (is_subclass_of($workflow, WorkflowDefinition::class) &&
                !(new \ReflectionClass($workflow))->isAbstract()) {
                $workflows[] = new $workflow();
            }
        }
        return $workflows;
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['workflow'];
    }
}
