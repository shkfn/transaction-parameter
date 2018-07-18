<?php
namespace Shkfn\TransactionParameter;

use Shkfn\TransactionParameter\Contracts\Transaction as TransactionInterface;
use Shkfn\TransactionParameter\Contracts\Storage;
/**
 *
 */
class Transaction implements TransactionInterface
{
    /** @var Storage  */
    protected $storage;


    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * トランザクションキーを発行し、セッションにストア領域を確保。キー返却。
     * 引数ありの場合はload()を実行。
     * @param string|null $token
     * @return string|bool トランザクションキー|キーがなければloadの判定結果
     */
    public function start($token = null)
    {

        if (is_null($token)) {
            return $this->storage->open();
        }

        return $this->load($token);

    }

    /**
     * startのエイリアス
     * @param string|null $token
     * @return string|bool
     */
    public function open($token = null)
    {
        return $this->start($token);
    }

    /**
     * キーストアにトランザクションキーが存在しているか判定。
     * @param  string $token トランザクションキー
     * @return bool                 キーの存在確認結果
     */
    public function load($token)
    {
        return $this->storage->load($token);
    }

    /**
     * 対象キーと格納データを削除。
     * @return void
     */
    public function stop()
    {
        $this->storage->close();
    }

    /**
     * stopのエイリアス
     * @return void
     */
    public function close()
    {
        $this->stop();
    }

    /**
     * 対象キー、タグのストレージ領域に値を格納
     * @param  array $param 格納する任意の連想配列
     * @param  null|string $tag タグ
     * @return void
     */
    public function put($param, $tag = null)
    {
        $this->storage->put($param, $tag);
    }

    /**
     * 対象キー、タグのストレージ領域に値を追加。同名パラメータは追加側の内容で上書きされる。
     * @param array $param
     * @param null|string $tag タグ
     * @return void
     */
    public function push($param, $tag = null)
    {
        $this->storage->push($param, $tag);
    }

    /**
     * 対象キーのストレージ領域から値を取得
     * @param  null|string $tag タグ
     * @return array
     */
    public function get($tag = null)
    {
        return $this->storage->get($tag);
    }

    /**
     * 対象キーのストレージ領域から値を取得。取得後は削除。
     * @param  null|string $tag タグ
     * @return array
     */
    public function pull($tag = null)
    {
        return $this->storage->pull($tag);
    }
}