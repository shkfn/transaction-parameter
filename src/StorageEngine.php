<?php
namespace Shkfn\TransactionParameter;
/**
 *
 */
interface StorageEngine
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