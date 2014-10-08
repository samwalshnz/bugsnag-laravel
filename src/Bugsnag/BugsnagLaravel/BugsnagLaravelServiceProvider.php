<?php namespace Bugsnag\BugsnagLaravel;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Exception\Handler;

class BugsnagLaravelServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @param  Handler  $handler
     *
     * @return void
     */
    public function boot(Handler $handler)
    {
        $this->package('bugsnag/bugsnag-laravel', 'bugsnag');

        $app = $this->app;

        // Register for exception handling
        $handle->error(function (\Exception $exception) use ($app) {
            $app['bugsnag']->notifyException($exception);
        });

        // Register for fatal error handling
        $handle->fatal(function ($exception) use ($app) {
            $app['bugsnag']->notifyException($exception);
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('bugsnag', function ($app) {
            $config = $app['config']['bugsnag'] ?: $app['config']['bugsnag::config'];

            $client = new \Bugsnag_Client($config['api_key']);
            $client->setStripPath(base_path());
            $client->setProjectRoot(app_path());
            $client->setAutoNotify(false);
            $client->setBatchSending(false);
            $client->setReleaseStage($app->environment());
            $client->setNotifier(array(
                'name'    => 'Bugsnag Laravel',
                'version' => '1.0.10',
                'url'     => 'https://github.com/bugsnag/bugsnag-laravel'
            ));

            if (is_array($stages = $config['notify_release_stages'])) {
                $client->setNotifyReleaseStages($stages);
            }

            return $client;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array("bugsnag");
    }
}
