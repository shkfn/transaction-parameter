<?php
namespace Shkfn\TransactionParameter\Contracts;
/**
 *
 */
interface Transaction
{
    public function start($key = null);
    public function open($key = null);
    public function load($key);
    public function stop();
    public function close();
    public function put($param, $tag = null);
    public function push($param, $tag = null);
    public function get($tag = null);
    public function pull($tag = null);
}