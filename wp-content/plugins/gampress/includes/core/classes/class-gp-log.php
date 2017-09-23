<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/7/19
 * Time: 21:31
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

class GP_Log {

    private static $instance = null;

    private function __construct(){}

    private function __clone(){}

    public static function Init( $level = 15 ) {
        if(!self::$instance instanceof self) {
            $handler = new GP_FileLog(  GP_LOG_DIR . '/' . date('Y-m-d') . '.log' );
            self::$instance = new self();
            self::$instance->__setHandle($handler);
            self::$instance->__setLevel($level);
        }
        return self::$instance;
    }

    private function __setHandle($handler) {
        $this->handler = $handler;
    }

    private function __setLevel($level) {
        $this->level = $level;
    }

    protected function write($level,$msg) {
        if (($level & $this->level) == $level) {
            $msg = '[' . date('Y-m-d H:i:s') . '][' . $this->getLevelStr($level) . '] ' . $msg . "\n";
            $this->handler->write($msg);
        }
    }

    private function getLevelStr($level) {
        switch ($level) {
            case 1:
                return 'debug';
                break;
            case 2:
                return 'info';
                break;
            case 4:
                return 'warn';
                break;
            case 8:
                return 'error';
                break;
            default:
        }
    }

    public static function DEBUG($msg) {
        self::$instance->write(1, $msg);
    }

    public static function WARN($msg) {
        self::$instance->write(4, $msg);
    }

    public static function ERROR($msg) {
        $debugInfo = debug_backtrace();
        $stack = "[";
        foreach ($debugInfo as $key => $val) {
            if (array_key_exists("file", $val)) {
                $stack .= ",file:" . $val["file"];
            }
            if (array_key_exists("line", $val)) {
                $stack .= ",line:" . $val["line"];
            }
            if (array_key_exists("function", $val)) {
                $stack .= ",function:" . $val["function"];
            }
        }
        $stack .= "]";
        self::$instance->write(8, $stack . $msg);
    }

    public static function INFO($msg) {
        self::$instance->write(2, $msg);
    }

    public static function TRACE() {
        self::$instance->write( 2, sprintf('trace,%1$s,%2$s,%3$s,%4$s,%5$s',
            gp_loggedin_user_id(),
            gp_get_loggedin_user_displayname(),
            gp_format_time( time() ),
            urldecode( $_SERVER['REQUEST_URI'] ),
            get_remote_ip() ) );
    }
}