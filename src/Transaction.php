<?php
namespace Shkfn\TransactionParameter;
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
     * @param string|null $key
     * @return string|bool トランザクションキー|キーがなければloadの判定結果
     */
    public function start($key = null)
    {

        if (is_null($key)) {
            return $this->storage->open();
        }

        return $this->load($key);

    }

    /**
     * startのエイリアス
     * @param string|null $key
     * @return string|bool
     */
    public function open($key = null)
    {
        return call_user_func_array([$this, 'start'], func_get_args());
    }

    /**
     * キーストアにトランザクションキーが存在しているか判定。
     * @param  string $key トランザクションキー
     * @return bool                 キーの存在確認結果
     */
    public function load($key)
    {
        return $this->storage->load($key);
    }

    /**
     * 対象キーと格納データを削除。
     * @return void
     */
    public function stop()
    {
        call_user_func([$this, 'close']);
    }

    /**
     * stopのエイリアス
     * @return void
     */
    public function close()
    {
        $this->storage->close();
    }

    /**
     * 対象キー、タグのストレージ領域に値を格納
     * @param  array $param 格納する任意の連想配列
     * @param  null|string $tag タグ
     * @return void
     */
    public function put($param, $tag = Storage::DEFAULT_TAG)
    {
        return $this->storage->put($param, $tag);
    }

    /**
     * 対象キー、タグのストレージ領域に値を追加。同名パラメータは追加側の内容で上書きされる。
     * @param array $param
     * @param null|string $tag タグ
     * @return void
     */
    public function push($param, $tag = Storage::DEFAULT_TAG)
    {
        return $this->storage->push($param, $tag);
    }

    /**
     * 対象キーのストレージ領域から値を取得
     * @param  null|string $tag タグ
     * @return array
     */
    public function get($tag = Storage::DEFAULT_TAG)
    {
        return $this->storage->get($tag);
    }

    /**
     * 対象キーのストレージ領域から値を取得。取得後は削除。
     * @param  null|string $tag タグ
     * @return array
     */
    public function pull($tag = Storage::DEFAULT_TAG)
    {
        return $this->storage->pull($tag);
    }
}