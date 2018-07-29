<?php

use Shkfn\TransactionParameter\Transaction;

class TransactionTest extends Orchestra\Testbench\TestCase
{
    /** @var Transaction */
    protected $transaction;

    protected function getPackageProviders($app)
    {
        return [Shkfn\TransactionParameter\Transaction::class];
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
            'namespace' => '_transaction',
            // トークン長
            'token_length' => 20,
            // 管理する最大トークン数
            'limit_of_tokens' => 10
        ]);
    }

    public function setUp()
    {
        parent::setUp();

        $this->transaction = $this->app->make(Transaction::class);
    }

    public function tearDown()
    {
        parent::tearDown();
        session()->forget('_transaction');
    }

    public function test__construct()
    {

    }

    public function testStart()
    {
        $token = $this->transaction->start();
        $this->assertInternalType('string', $token);
        return $token;
    }

    /**
     * @depends testStart
     * @param string $token
     */
    public function testStartWithToken($token)
    {
        $this->assertTrue($this->transaction->start($token));
        $this->assertFalse($this->transaction->start('dummy'));
    }

    public function testPut()
    {

    }

    public function testLoad()
    {

    }

    public function testGet()
    {

    }

    public function testStop()
    {

    }

    public function testPull()
    {

    }

    public function testOpen()
    {

    }



    public function testClose()
    {

    }

    public function testPush()
    {

    }
}
