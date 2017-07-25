<?php

/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/7/19
 * Time: 21:48
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

class GP_FileLog {
    private $handle = null;

    public function __construct($file = '')
    {
        $this->handle = fopen($file, 'a');
    }

    public function write($msg)
    {
        fwrite($this->handle, $msg, 4096);
    }

    public function __destruct()
    {
        fclose($this->handle);
    }
}