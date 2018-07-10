<?php
namespace Shkfn\TransactionParameter;

use Illuminate\Support\ServiceProvider;

class TransactionParameterServiceProvider extends ServiceProvider
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
        $configPath = __DIR__ . '/../config/transaction-storage.php';
        if (function_exists('config_path')) {
            $publishPath = config_path('transaction-storage.php');
        } else {
            $publishPath = base_path('config/transaction-storage.php');
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
        $configPath = __DIR__ . '/../config/transaction-storage.php';
        $this->mergeConfigFrom($configPath, 'transaction-storage');

        // ストレージ領域制御クラスのバインド
        $this->app->singleton(
            SessionStorageEngine::class,
            function($app) {
                return new SessionStorageEngine(
                    $app['session.store'],
                    $app['config']->get('transaction-storage.namespace'),
                    $app['config']->get('transaction-storage.key_length'),
                    $app['config']->get('transaction-storage.store_max')
                );
            }
        );
        // ストレージ領域制御インターフェースのバインド
        $this->app->bind(
            StorageEngine::class,
            SessionStorageEngine::class
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
