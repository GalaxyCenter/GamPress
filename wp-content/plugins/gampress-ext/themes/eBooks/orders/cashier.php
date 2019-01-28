<?php
/**
 * Created by PhpStorm.
 * User: kuibo
 * Date: 2017/4/27
 * Time: 21:52
 */

$order_id = gp_action_variable( 0 );
?>

<a href="/pays/create/alipay?order_id=<?php echo $order_id;?>&product_name=test&product_fee=99&product_url=url&product_description=desc">pay</a>
