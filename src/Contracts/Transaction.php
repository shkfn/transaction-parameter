<?php
namespace Shkfn\TransactionParameter\Contracts;
/**
 *
 */
interface Transaction
{
    /**
     * トランザクション開始|再開
     * @param string|null $token
     * @return string|bool
     */
    public function start($token = null);

    /**
     * startのエイリアス
     * @param string|null $token
     * @return string|bool
     */
    public function open($token = null);

    /**
     * トランザクション再開
     * @param string $token
     * @return bool
     */
    public function load($token);

    /**
     * トランザクション停止
     * @return void
     */
    public function stop();

    /**
     * stopのエイリアス
     * @return void
     */
    public function close();

    /**
     * パラメータ保存
     * @param array $params
     * @param string|null $tag
     * @return void
     */
    public function put($params, $tag = null);

    /**
     * パラメータ追加
     * @param array $params
     * @param string|null $tag
     * @return void
     */
    public function push($params, $tag = null);

    /**
     * パラメータ取得
     * @param string|null $tag
     * @return array
     */
    public function get($tag = null);

    /**
     * パラメータ取得後に削除
     * @param string|null $tag
     * @return array
     */
    public function pull($tag = null);
}