<?php
namespace Shkfn\TransactionParameter;

class Facade extends \Illuminate\Support\Facades\Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return Transaction::class; // バインド名称と合わせる
    }
}
