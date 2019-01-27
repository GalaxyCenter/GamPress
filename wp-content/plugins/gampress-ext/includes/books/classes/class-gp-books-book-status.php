<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/4/3
 * Time: 1:54
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * GamPress Video object.
 *
 * @since 1.6.0
 */
class GP_Books_Book_Status {
    public $id;
    public $book_id;

    /** @var  作品总字数 */
    public $words;

    /** @var  作品上传时间 */
    public $update_time;

    /** @var  作品计费时间 */
    public $charge_time;

    /** @var  作品屏蔽时间 */
    public $banned_time;

    /** @var  作品屏蔽理由 */
    public $banned_description;

    /** @var  作品恢复上线时间 */
    public $approved_time;

    public function __construct( $id = null, $args = array() ) {
        if ( !empty( $id ) ) {
            $this->id = (int) $id;
            $this->populate();
        }
    }

}