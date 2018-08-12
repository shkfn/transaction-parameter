<?php

use Shkfn\TransactionParameter\Transaction;

class TransactionTest extends Orchestra\Testbench\TestCase
{
    /** @var Transaction */
    protected $transaction;

    protected $option = [
        // ルートキー名
        'namespace' => '_transaction',
        // トークン長
        'token_length' => 20,
        // 管理する最大トークン数
        'limit_of_tokens' => 10
    ];

    protected static $session = [];

    protected function getPackageProviders($app)
    {
        return [Shkfn\TransactionParameter\TransactionServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Transaction' => Shkfn\TransactionParameter\Facade::class
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('transaction-parameter', [
            // ルートキー名
            'namespace' => $this->option['namespace'],
            // トークン長
            'token_length' => $this->option['token_length'],
            // 管理する最大トークン数
            'limit_of_tokens' => $this->option['limit_of_tokens']
        ]);
    }

    public function setUp()
    {
        parent::setUp();
//        $this->session(self::$session);
        $this->transaction = $this->app->make(Transaction::class);
    }

    public function tearDown()
    {
//        self::$session = app('session')->all();
        parent::tearDown();
    }

//    public function test__construct()
//    {
//
//    }

    public function testStart()
    {
        $this->commonTestStartOpen('start');
    }

    public function testOpen()
    {
        $this->commonTestStartOpen('open');
    }

    protected function commonTestStartOpen($method)
    {
        $token = $this->transaction->{$method}();
        $this->assertInternalType('string', $token);
        $this->assertRegExp('/[a-zA-Z0-9]{'.$this->option['token_length'].'}/', $token);
        $this->assertTrue($this->transaction->{$method}($token));
        $this->assertFalse($this->transaction->{$method}('dummy'));
        $session = app('session')->all();
        $this->assertArrayHasKey($this->option['namespace'], $session);
        $this->assertArraySubset(
            [$token => ['_default' => null]],
            $session[$this->option['namespace']]
        );
    }

    public function testLoad()
    {
        $token = $this->transaction->start();
        $this->assertTrue($this->transaction->load($token), 'load with token invalid');
        $this->assertFalse($this->transaction->load('dummy'), 'load with wrong token invalid');
    }

    public function testPut()
    {
        $this->transaction->start();
        $this->transaction->put([
            'param1' => 1,
            'param2' => 2,
        ]);
        $this->assertArraySubset(
            [
                'param1' => 1,
                'param2' => 2,
            ],
            $this->transaction->get(),
            true,
            'put no tag invalid'
        );
        // タグ付き
        $this->transaction->put([
            'param3' => 3,
            'param4' => 4,
        ], 'tag1');
        $this->transaction->put([
            'param5' => 5,
            'param6' => 6,
        ], 'tag2');
        $this->assertArraySubset(
            [
                'param3' => 3,
                'param4' => 4,
            ],
            $this->transaction->get('tag1'),
            true,
            'put tag1 invalid'
        );
        $this->assertArraySubset(
            [
                'param5' => 5,
                'param6' => 6,
            ],
            $this->transaction->get('tag2'),
            true,
            'put tag2 invalid'
        );
        $this->assertArraySubset(
            [
                'param1' => 1,
                'param2' => 2,
            ],
            $this->transaction->get(),
            true,
            'タグ付きput後にタグ指定無しの値が不正になった'
        );
        $this->transaction->put([
            'param100' => 100,
            'param200' => 200,
        ]);
        $this->assertArraySubset(
            [
                'param100' => 100,
                'param200' => 200,
            ],
            $this->transaction->get(),
            true,
            'パラメータが上書きになっていない'
        );
    }

    public function testPull()
    {
        $this->transaction->start();
        $this->transaction->put([
            'param1' => 1,
            'param2' => 2,
        ]);
        $this->transaction->put([
            'param3' => 3,
            'param4' => 4,
        ], 'tag1');
        $this->assertArraySubset(
            [
                'param1' => 1,
                'param2' => 2,
            ],
            $this->transaction->pull(),
            true,
            'pull no tag invalid'
        );
        $this->assertArraySubset(
            [],
            $this->transaction->get(),
            true,
            'pull後に値が消えていない'
        );
        $this->assertArraySubset(
            [
                'param3' => 3,
                'param4' => 4,
            ],
            $this->transaction->pull('tag1'),
            true,
            'pull tag1 invalid'
        );
        $this->assertArraySubset(
            [],
            $this->transaction->get('tag1'),
            true,
            'タグ指定pull後に値が消えていない'
        );
    }

    public function testPush()
    {
        $this->transaction->start();
        $this->transaction->push([
            'param1' => 1,
            'param2' => 2,
        ]);
        $this->transaction->push([
            'param3' => 3,
            'param4' => 4,
        ]);
        $this->assertArraySubset(
            [
                'param1' => 1,
                'param2' => 2,
                'param3' => 3,
                'param4' => 4,
            ],
            $this->transaction->get(),
            true,
            'push no tag invalid'
        );

        $this->transaction->push([
            'param1' => 'merge1',
            'param3' => 'merge3',
            'param5' => 5,
        ]);
        $this->assertArraySubset(
            [
                'param1' => 'merge1',
                'param2' => 2,
                'param3' => 'merge3',
                'param4' => 4,
                'param5' => 5,
            ],
            $this->transaction->get(),
            true,
            'push merge no tag invalid'
        );

        $this->transaction->push([
            'param1' => 1,
            'param2' => 2,
        ], 'tag1');
        $this->transaction->push([
            'param3' => 3,
            'param4' => 4,
        ], 'tag1');
        $this->assertArraySubset(
            [
                'param1' => 1,
                'param2' => 2,
                'param3' => 3,
                'param4' => 4,
            ],
            $this->transaction->get('tag1'),
            true,
            'push tag1 invalid'
        );

        $this->transaction->push([
            'param1' => 'merge1',
            'param3' => 'merge3',
            'param5' => 5,
        ], 'tag1');
        $this->assertArraySubset(
            [
                'param1' => 'merge1',
                'param2' => 2,
                'param3' => 'merge3',
                'param4' => 4,
                'param5' => 5,
            ],
            $this->transaction->get('tag1'),
            true,
            'push merge tag1 invalid'
        );
    }
//    public function testGet()
//    {
//
//    }
//
//    public function testStop()
//    {
//
//    }
//
//
//
//
//
//    public function testClose()
//    {
//
//    }
//
}
