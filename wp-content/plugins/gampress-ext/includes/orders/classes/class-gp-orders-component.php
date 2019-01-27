<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/4/21
 * Time: 23:33
 */


defined( 'ABSPATH' ) || exit;

class GP_Orders_Component extends GP_Component {
    public function __construct() {
        parent::start(
            'orders',
            __( 'Orders', 'gampress' ),
            GP_EXT_INCLUDES_DIR,
            array(
                'adminbar_myaccount_order' => 20
            )
        );
    }

    public function includes( $includes = array() ) {
        $includes = array(
            'screens',
            'functions',
            'actions',
            'filters',
            'ajax',
            'template'
        );

        if ( ! gampress()->do_autoload ) {
            $includes[] = 'classes';
        }

        if ( is_admin() ) {
            $includes[] = 'admin';
        }

        parent::includes( $includes );
    }

    public function setup_globals( $args = array() ) {
        $gp = gampress();

        if ( !defined( 'GP_ORDERS_SLUG' ) ) {
            define( 'GP_ORDERS_SLUG', $this->id );
        }

        /**
         * 未提交 （此状态订单可以修改）
         *
         * @var mixed
         *
         */
        define( 'GP_ORDER_NORMAL', 1 );

        /**
         * 锁定 （此状态不能付款，需等后台人员确认后才能进入支付流程）
         *
         */
        define( 'GP_ORDER_LOCKED', 2 );

        /**
         * 已经提交（此状态未等待付款）
         *
         */
        define( 'GP_ORDER_SUBMIT', 3 );

        /**
         * 已付款
         *
         * @var mixed
         *
         */
        define( 'GP_ORDER_PAID', 4 );

        /**
         * 确认中 （支付后，后台人员尚未确认）
         *
         * @var mixed
         *
         */
        define( 'GP_ORDER_CONFIRMING', 5 );


        /**
         * 已确认 （支付后，后台人员已经确认）
         *
         * @var mixed
         *
         */
        define( 'GP_ORDER_CONFIRMED', 6 );


        /**
         * 已经成交
         *
         * @var mixed
         *
         */
        define( 'GP_ORDER_COMPLETE', 7 );

        /**
         * 申请取消
         *
         * @var mixed
         *
         */
        define( 'GP_ORDER_CANCEL', 8 );

        /**
         * 申请变更
         *
         * @var mixed
         *
         */
        define( 'GP_ORDER_MODIFY', 9 );

        /**
         * 已取消
         *
         * @var mixed
         *
         */
        define( 'GP_ORDER_CANCELED', 10 );

        $global_tables = array(
            'table_name'                => $gp->table_prefix . 'gp_orders',
            'table_meta_name'           => $gp->table_prefix . 'gp_ordermeta',
            'table_name_coin_bills'     => $gp->table_prefix . 'gp_coin_bills',
            'table_name_tickets'        => $gp->table_prefix . 'gp_tickets'
        );

        $args = array(
            'slug'                  => GP_ORDERS_SLUG,
            'global_tables'         => $global_tables
        );

        parent::setup_globals( $args );

        $gp->current_action   = gp_current_action();
        if ( gp_is_orders_component() && $order_id = GP_Orders_Order::order_exists( gp_action_variable( 0 ) ) ) {
            $gp->is_single_item  = true;
            $this->current_item = $this->current_order = gp_orders_get_order( $order_id );
        }
        //// 有逻辑错误,访问其他组件url的时候会造成404.
//        else {
//            $this->current_order = false;
//            $this->current_item = urldecode( gp_action_variable( 0 ) );
//
//            if ( !empty( $this->current_item ) ) {
//                $this->current_page = gp_action_variable( 1 );
//                if ( !empty( $this->current_page ) && !is_numeric( $this->current_page ) )  {
//                    gp_do_404();
//                    die;
//                }
//            }
//            if ( empty( $this->current_page ) )
//                $this->current_page = 1;
//        }
    }
}