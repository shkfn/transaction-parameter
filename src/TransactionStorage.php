<?php

namespace Shkfn\TransactionParameter;

trait TransactionStorage
{

    /**
     * トランザクションキーを発行し、セッションにストレージ領域を確保。キー返却。
     * @return string トランザクションキー
     */
    protected function startTransaction()
    {
        $store = session()->get('_transaction',[]);
        $transactionKey = str_random(config('const.transaction_key_length'));
        $store += [
            $transactionKey => [
                'start' => request()->getUri(),
                'strage' => null,
            ]
        ];
        $store = array_slice($store,0,config('const.transaction_store_max')); // 上限以上は古い方から破棄

        session()->put(['_transaction' => $store]);
        return $transactionKey;
    }

    /**
     * ストレージ領域にトランザクションキーが存在しているか判定。
     * @param  string $transactionKey トランザクションキー
     * @return bool                 キーの存在確認結果
     */
    protected function continueTransaction($transactionKey)
    {
        // キー存在確認
        if (session()->has('_transaction.'.$transactionKey) ) 
            return true;

        return false;
    }

    /**
     * キーの対象ストレージ領域を削除。
     * @param  string $transactionKey トランザクションキー
     * @return void
     */
    protected function endTransaction($transactionKey)
    {
        session()->forget('_transaction.'.$transactionKey);
    }

    /**
     * キーの対象ストレージ領域に値を格納
     * @param  string $transactionKey トランザクションキー
     * @param  array $values         格納する任意の連想配列
     * @return void
     */
    protected function putTransactionStorage($transactionKey, $values)
    {
        $strage = session()->get('_transaction.'.$transactionKey, null);
        if ($strage) {
            session()->put('_transaction.'.$transactionKey.'.strage', $values);
        }
    }

    /**
     * キーの対象ストレージ領域から値を取得
     * @param  string $transactionKey トランザクションキー
     * @return array
     */
    protected function getTransactionStorage($transactionKey)
    {
        return session()->get('_transaction.'.$transactionKey.'.strage', null);
    }

}
