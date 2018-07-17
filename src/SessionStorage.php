<?php
namespace Shkfn\TransactionParameter;

use Illuminate\Contracts\Session\Session;
/**
 *
 */
class SessionStorage implements StorageEngine
{
    /** @var string */
    protected $namespace;
    /** @var  int */
    protected $token_length;
    /** @var  int */
    protected $limit_of_tokens;
    /** @var  string */
    protected $key;
    /**
     * トランザクション処理用のストレージ領域にLaravelのセッションを使用する.
     *
     * @var \Illuminate\Contracts\Session\Session
     */
    protected $session;

    /**
     * セッションラッパークラス生成
     *
     * @param string $namespace
     * @param  \Illuminate\Contracts\Session\Session  $session
     * @return void
     */
    public function __construct(Session $session, $namespace, $token_length, $limit_of_tokens)
    {
        $this->session = $session;
        $this->namespace = $namespace;
        $this->token_length = $token_length;
        $this->limit_of_tokens = $limit_of_tokens;
    }

    public function open()
    {
        // キー無しはストア領域確保して開始準備
        $store = $this->session->get($this->namespace,[]);
        $this->key = str_random($this->token_length);
        $storage = [self::DEFAULT_TAG => null];
        $store += [
            $this->key => $storage
        ];
        if ( count($store) >= $this->limit_of_tokens) {
            $store = array_slice($store, 1, $this->limit_of_tokens); // 上限以上は古い方から破棄
        }
        $this->session->put($this->namespace, $store);
        return $this->key;
    }

    /**
     * 指定のキーをプロパティセット。存在確認結果を返却。
     * @param string $key
     * @return bool
     */
    public function load($key)
    {
        $this->key = $key;
        return $this->session->has($this->namespace.'.'.$this->key);
    }

    /**
     * ストレージ領域に格納しているパラメータを取得
     * @param null $tag
     * @return array
     */
    public function get($tag = self::DEFAULT_TAG)
    {
        return $this->session->get($this->namespace . '.' . $this->key . '.' . $tag, []);
    }

    public function pull($tag = self::DEFAULT_TAG)
    {
        return $this->session->pull($this->namespace.'.'.$this->key.'.'.$tag, []);
    }

    /**
     * パラメータ格納。入力済みの値がある場合は上書き
     * @param $param
     * @param null $tag
     * @return void
     */
    public function put($param, $tag = self::DEFAULT_TAG)
    {
        $this->session->put($this->namespace.'.'.$this->key.'.'.$tag, $param);
        return;
    }

    public function push($param, $tag = self::DEFAULT_TAG)
    {
        $stored = $this->get($tag);
        if (is_null($stored)) {
            $stored = [];
        }
        $this->put(array_merge($stored, $param), $tag);
    }

    public function has($key)
    {
        $this->key = $key;
        return $this->session->has($this->namespace.'.'.$key);
    }

    public function close()
    {
        $this->session->forget($this->namespace.'.'.$this->key);
    }
}