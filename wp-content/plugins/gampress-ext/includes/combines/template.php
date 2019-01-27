<?php

/**
 * GamPress Core Template Functions
 * 的
 * @package gampressustom
 * @subpackage Functions
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function gp_is_combines_component() {
    return gp_is_current_component( 'combines' );
}

function gp_get_current_combine() {
    $gp = gampress();
    
    return $gp->combines->current_combine;
}

function gp_combine_title( $combine ) {
    echo gp_get_combine_title( $combine );
}
    function gp_get_combine_title( $combine ) {
        return $combine->title;
    }
    
function gp_combine_size( $combine ) {
    echo gp_get_combine_size( $combine );
}
    function gp_get_combine_size( $combine ) {
        return $combine->size;
    }
    
function gp_combine_description( $combine ) {
    echo gp_get_combine_description( $combine );
}
    function gp_get_combine_description( $combine ) {
        //$description = str_replace( '如果下载速度过慢，请添加微信号185855023（昵称：小陈陈）咨询获取百余种外刊持续快速更新的方法和百度网盘等便捷下载方式，加微信号185855023还可获取更多英文原版杂志和报纸，朋友圈每天都会发布最新杂志的更新说明。欢迎随时添加微信，外刊已经持续更新了数年，且会一直持续更新下去！', '', $combine->description );
       // $description = str_replace( '敬请添加微信号592369672（昵称：小陈陈）咨询获取百余种外刊持续快速更新的方法和百度网盘等便捷下载方式，加微信号592369672还可获取更多英文原版杂志和报纸，朋友圈每天都会发布最新杂志的更新说明。欢迎随时添加微信，外刊已经持续更新了数年，且会一直持续更新下去！', '', $description );
        $description = preg_replace( '/如果下载速度过慢，|微信号\d+|咨询获取百余种外刊持续快速更新的方法和百度网盘等便捷下载方式|请添|（昵称：.*?）|朋友圈每天都会发布最新杂志的更新说明|ed2000|更多英文原版杂志和报纸|欢迎随时添加微信|关注朋友圈更新|更多外刊资源的持续更新情况|百余种外刊持续快速更新的方法和百度网盘等便捷下载方式|咨询获取|外刊已经持续更新了数年|且会一直持续更新下去|众筹VIP|百度云网盘等下载方式|还可获取此类外刊的其他下载方式/', '', $combine->description);

        return $description;
    }
    
function gp_combine_tags( $combine ) {
    $tags = gp_get_combine_tags( $combine );
    $tags = explode( ',', $tags );
    
    $html = array();
    foreach( $tags as $tag ) {
        $link = gp_get_combine_tag_permalink( $tag );
        $html[] = "<a href='{$link}' target=‘_blank’>{$tag}</a>";
    }
    $html = join( ', ', $html );
    echo $html;
}
    function gp_get_combine_tags( $combine ) {
        return $combine->tags;
    }
    
function gp_combine_type( $combine ) {
    $links = $combine->links;
    
    $html = '';
    if ( count( $links->ed2ks ) != 0 )
        $html .= '<i>ed2k</i>';
        
    if ( count( $links->magnets ) != 0 )
        $html .= '<i>magnet</i>';
        
    echo $html;
}

function gp_combine_post_date( $combine, $pattern = 'Y-m-d' ) {
    $date = gp_get_combine_post_date( $combine );
    echo date( $pattern, $date );
}
    function gp_get_combine_post_date( $combine ) {
        return $combine->post_date;
    }

function gp_combine_excerpt( $combine ) {
    echo gp_get_combine_excerpt( $combine );
}
function gp_get_combine_excerpt( $combine ) {
        $description = $combine->description;
        $description = preg_replace( '/<[^>]+>/', '', $description );
        
        $len = strlen( $description );
        $len = $len > 100 ? 100 : $len;
        $description = mb_substr( $description, 0, $len, 'utf-8');
        
        return $description;
    }
    
function gp_combine_term( $id, $level = 0 ) {
    $term = gp_get_combine_term( $id, $level );
    
    echo $term->name;
}

function gp_get_combine_term( $id, $level = 0 ) {
    $terms = gp_get_combine_terms( $id );
    
    if ( empty( $terms ) )
        return false;
        
    if ( $level == 0 )
        return $terms[0];
        
    $max_level = count( $terms );
    if ( $level == -1 )
        return $terms[$max_level-1];
    
    return $terms[$level];
}

function gp_get_combine_terms( $id ) {
    $term_ids = GP_Combines_Combine::get_term_ids( $id );
    
    $terms = array();
    foreach( (array) $term_ids as $term_id )
        $terms[] = get_term_by( 'term_id', $term_id, 'combine' );
        
    return $terms;
}

function gp_combine_term_name( $id ) {
    echo gp_get_combine_term_name( $id );
}

    function gp_get_combine_term_name( $id ) {
        $term = gp_get_combine_term( $id, -1 );
        return $term->name;
    }

function gp_combine_term_permalink( $term ) {
    echo gp_get_combine_term_permalink( $term );
}

    function gp_get_combine_term_permalink( $term ) {
        return "/combines/category/{$term->name}";
    }

function gp_combine_tag_permalink( $tag ) {
    echo gp_get_combine_tag_permalink( $tag );
}

    function gp_get_combine_tag_permalink( $tag ) {
        return "https://www.baidu.com/s?ie=UTF-8&wd=site%3Aoutwiki.com%20{$tag}";
    }


function gp_combine_permalink( $combine ) {
    echo gp_get_combine_permalink( $combine );
}

    function gp_get_combine_permalink( $combine ) {
        return "/combines/show/{$combine->id}";
    }
    
function gp_combine_ed2k_table( $ed2ks ) {
?>
    <table class="table table-striped">
      <thead>
        <tr>
          <th><input type="checkbox" id="cb_ed2k"/></th>
          <th>标题</th>
          <th>大小</th>
        </tr>
      </thead>
      <tbody>
        <?php        
        foreach( $ed2ks as $ed2k ) : 
        ?>
        <tr>
          <td><input type="checkbox" name="cb_ed2k"/></td>
          <td><a href="<?php echo preg_replace( '/\(ED2000.COM\)/', '(WIKI.COM)', $ed2k->link ); ?>"><?php echo $ed2k->title;?></a></td>
          <td><?php echo $ed2k->size;?></td>
        </tr>
        <?php
        endforeach;?>
      </tbody>
    </table>
<?php
}

function gp_combine_magnet_table( $magnets ) {
?>
    <table class="table table-striped">
      <thead>
        <tr>
          <th><input type="checkbox" id="cb_ed2k"/></th>
          <th>标题</th>
        </tr>
      </thead>
      <tbody>
        <?php        
        foreach( $magnets as $magnet ) : 
        ?>
        <tr>
          <td><input type="checkbox" name="cb_ed2k"/></td>
          <td><a href="<?php echo $magnet->link;?>"><?php echo $magnet->title;?></a></td>
        </tr>
        <?php
        endforeach;?>
      </tbody>
    </table>
<?php
}