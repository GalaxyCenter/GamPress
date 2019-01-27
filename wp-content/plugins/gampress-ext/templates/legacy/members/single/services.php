<div class="item-list-tabs no-ajax" id="subnav" role="navigation">
    <ul>
        <?php bp_get_options_nav(); ?>
    </ul>
</div>

<div class="woo-content">
    <?php switch ( bp_current_action() ) :

    // Edit
    case 'services'   :
        gp_get_template_part( 'members/single/services/view' );
        break;

    // Change Avatar
    case 'edit' :
        gp_get_template_part( 'members/single/services/edit' );
        break;

    endswitch; ?>
</div>
