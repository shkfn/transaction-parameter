# Transaction Parameter

## 機能概要

リクエストパラメータを一時格納、取り出しを行う手順を簡易化するLaravel用ライブラリです。Laravel 5.5以上で動作確認をしています。

バリデーション後にパラメータを保存、確認画面やDB保存時に取り出すといった運用を想定しています。

コントローラのコンストラクタ又はメソッドにDIして使用します。

- 現在はセッションを使ってデータを格納する仕様です。
- Laravelのセッション管理を使用します。
- 格納データにタグを設定できるので、入力画面が複数段階あるような場合のデータ格納にも対応します。

## インストールと準備

プロジェクトの composer.json にライブラリのリポジトリを追加します。現状はPackagistに載せていませんので、直接githubのリポジトリを指定します。

```json
"repositories": [
    {
        "type": "git",
        "url": "https://github.com/shkfn/transaction-parameter"
    }
]
```

composerを使ってインストール。

```shell
composer require shkfn/transaction-parameter:^1.0
```

サービスプロバイダを config/app.php の providers に設定。

```php
Shkfn\TransactionParameter\TransactionServiceProvider::class,
```

ファサードを使いたい場合は config/app.php の aliases に設定。使用しないのであれば設定は必要ありません。

```php
'Transaction' => Shkfn\TransactionParameter\Facade::class,
```

コンフィグファイルのコピー。もしコマンドでコピーされない場合は、 ライブラリ内の config/transaction-parameter.php をフレームワークの config/transaction-parameter.php へ手動でコピーしてください。

```shell
php artisan vendor:publish --provider="Shkfn\TransactionParameter\TransactionServiceProvider" --tag=config
```

## 使い方とサンプルコード

シンプルな入力画面、確認画面、登録（各画面間はPRGパターンで遷移）という流れでの使用例です。

ルーティング

```php
// 入力画面表示
Route::get('input/{token?}', [
    'as' => 'input',
    'uses' => 'TransactionController@input'
]);
// 入力バリデーション
Route::post('input/validate/{token}', [
    'as' => 'validate_input',
    'uses' => 'TransactionController@validateInput'
]);
// 確認画面表示
Route::get('input/confirm/{token}', [
    'as' => 'confirm',
    'uses' => 'TransactionController@confirm'
]);
// データ登録
Route::post('input/register/{token}', [
    'as' => 'register',
    'uses' => 'TransactionController@register'
]);
```

コントローラ

```php
namespace App\Http\Controllers;

use Shkfn\TransactionParameter\Transaction;

class TransactionController extend Controller
{
    /** @var Transaction */
    protected $transaction;
    
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * 入力画面表示
     * @param string $token
     */
    public function input($token = null)
    {
        $param = null;
        if (is_null($token)) {
            $token = $this->transaction->start(); // tokenがnullか引数無しの場合に新しいtokenを発行して返却
        } else {
            // token付きで入力画面に戻ってきた場合にパラメータを引き出せる
            if ($this->transaction->start($token)) { // tokenが渡された場合は保存領域でtokenの存在確認をbool返却
                $params = $this->transaction->get(); // パラメータ取得。格納値が無い場合は空配列が返る。
            } else {
                return abort(404);
            }
        }
        return view('input', ['token' => $token,'params' => $params]) // tokenはルートパラメータとして使用
    }

    /**
     * 入力バリデーション
     * @param Request $request
     * @param string $token
     */
    public function validateInput(Request $request, $token)
    {
        // token毎に区切られた領域に保存
        if ($this->transaction->start($token)) {
            $params = $request->validated();
            $this->transaction->put($params); // バリデーション済みの値を保存。第2引数に文字列でタグを設定可能。タグを設定して保存した場合は、get時にもタグの指定が必要。
            return redirect('confirm');
        }
        return back();
    }

    /**
     * 確認画面表示
     * @param string $token
     */
    public function confirm($token)
    {
        if ($this->transaction->start($token)) { // tokenを使ってトランザクションを再開
            $params = $this->transaction->get();
            return view('confirm', ['params' => $params]);
        }
        return redirect('input'); // 入力画面やエラー画面等へリダイレクト
    }

    /**
     * 登録処理
     * @param string $token
     */
    public function register($token)
    {
        if ($this->transaction->start($token)) { // tokenを使ってトランザクションを再開
            $params = $this->transaction->get();
            Post::create($data); // DB登録
            $this->transaction->close(); // tokenの保存領域を明示的にクリアするメソッド
            return redirect('complete');
        }
        return redirect('input'); // 入力画面やエラー画面等へリダイレクト
    }
}
```

## リファレンス

### コンフィグ

- namespace

保存領域を確保する際に使用する、ルート名。通常は変更の必要はありませんが、名称が衝突するような場合に任意に変更してください。

- token_length

処理開始時に発行するトークンの文字数。

- limit_of_tokens

保存領域で管理するトークンの最大数。これを超えると、トークン発行時に古いトークンから削除されます。トークン毎に格納データは管理されるため、トークンの削除と同時に格納データも消失します。

### メソッド

- start(string $token = null) : string

トランザクションを開始（再開）する。トークンを発行し、セッションに保存領域を確保。発行したトークンを返却。null以外の引数を設定した場合は load()を実行します。

- open(string $token = null) : string

start()のエイリアス。

- load(string $token) : bool

保存領域に指定したトークンが存在しているか判定し結果を返却します。

- stop() : void

対象トークンを明示的に削除。保存領域も合わせて削除されます。

- close();

stop()のエイリアス。

- put(array $param, string $tag = null) : void

保存領域に第1引数のパラメータを保存する。常にメソッドコール時のパラメータで上書きするため、**このメソッドで保存値は追加されない。**追加第2引数でタグを設定可能。

- push(array $param, string $tag = null) : void

**保存済みのパラメータに対して第1引数のパラメータを追加する。同一キーの内容は後から設定したもので上書きされる。（array_merge相当）。**第2引数でタグを設定可能。

- get(string $tag = null) : array

保存したデータを取得する。第2引数でタグを指定可能。保存時にタグが指定されていた場合は、取得時にもタグの指定が必要です。

- pull(string $tag = null) : array

保存したデータを取得する。第2引数でタグを指定可能。取得後、格納データは削除されます。
