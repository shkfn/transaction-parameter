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
        $this->session(self::$session);
        $this->transaction = $this->app->make(Transaction::class);
    }

    public function tearDown()
    {
        self::$session = app('session')->all();
        parent::tearDown();
    }

//    public function test__construct()
//    {
//
//    }

    public function testStart()
    {
        $token = $this->transaction->start();
        $this->assertInternalType('string', $token);
        $this->assertRegExp('/[a-zA-Z0-9]{'.$this->option['token_length'].'}/', $token);
        $this->assertTrue($this->transaction->start($token));
        $this->assertFalse($this->transaction->start('dummy'));
        $session = app('session')->all();
        $this->assertArrayHasKey($this->option['namespace'], $session);
        $this->assertArraySubset(
            [$token => ['__default' => null]],
            $session[$this->option['namespace']]
        );
    }

//    public function testOpen()
//    {
//        $token = $this->transaction->open();
//        $this->assertInternalType('string', $token);
//        $this->assertRegExp('/[a-zA-Z0-9]{'.$this->option['token_length'].'}/', $token);
//        $this->assertTrue($this->transaction->open($token));
//        $this->assertFalse($this->transaction->open('dummy'));
//    }

//
//    public function testPut()
//    {
//
//    }
//
//    public function testLoad()
//    {
//
//    }
//
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
//    public function testPull()
//    {
//
//    }
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
}
