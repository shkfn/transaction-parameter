<?php
namespace Shkfn\TransactionParameter;

use Illuminate\Support\ServiceProvider;

class TransactionServiceProvider extends ServiceProvider
{

    /**
     * 遅延ロード.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $configPath = __DIR__ . '/../config/transaction-parameter.php';
        if (function_exists('config_path')) {
            $publishPath = config_path('transaction-parameter.php');
        } else {
            $publishPath = 'config/transaction-parameter.php';
        }
        $this->publishes([$configPath => $publishPath], 'config');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $configPath = __DIR__ . '/../config/transaction-parameter.php';
        $this->mergeConfigFrom($configPath, 'transaction-parameter');

        // ストレージ領域制御クラスのバインド
        $this->app->singleton(
            SessionStorage::class,
            function($app) {
                return new SessionStorage(
                    $app['session.store'],
                    $app['config']->get('transaction-parameter.namespace'),
                    $app['config']->get('transaction-parameter.token_length'),
                    $app['config']->get('transaction-parameter.limit_of_tokens')
                );
            }
        );
        // ストレージ領域制御インターフェースのバインド
        $this->app->bind(
            StorageEngine::class,
            SessionStorage::class
        );
        // トランザクション管理クラスのバインド
        $this->app->singleton(
            Transaction::class,
            function ($app) {
                return new Transaction($app[StorageEngine::class]);
            }
        );

    }

    /**
     * このプロバイダにより提供されるサービス
     *
     * @return array
     */
    public function provides()
    {
        return [Transaction::class];
    }

}
