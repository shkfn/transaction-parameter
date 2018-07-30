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
        $this->assertTrue($this->transaction->load('dummy'), 'load with wrong token invalid');
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
            'put invalid'
        );

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
            'put reuse invalid'
        );
    }

//    public function testPull()
//    {
//
//    }

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
//    public function testPush()
//    {
//
//    }
//    }
}
