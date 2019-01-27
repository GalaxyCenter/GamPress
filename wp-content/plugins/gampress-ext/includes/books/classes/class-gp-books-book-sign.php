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
class GP_Books_Book_Sign {
    public $id;

    public $book_id;

    /** @var  授权类型 */
    public $type;

    /** @var  签约时间 */
    public $sign_time;

    /** @var  签约时限 */
    public $deadline_time;

    /** @var  签约到期时间 */
    public $expire_time;

    /** @var  分成比例 */
    public $scale;

    public function __construct( $id = null, $args = array() ) {
        if ( !empty( $id ) ) {
            $this->id = (int) $id;
            $this->populate();
        }
    }

}