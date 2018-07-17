<?php
namespace Shkfn\TransactionParameter\Contracts;
/**
 *
 */
interface Storage
{
    const DEFAULT_TAG = '_default';

    public function open();
    public function load($key);
    public function get($tag);
    public function put($param, $tag);
    public function pull($tag);
    public function push($param, $tag);
    public function close();
}