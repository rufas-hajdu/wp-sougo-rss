<?php

global $post;

/*---------------------------------------------------------------------------------------------
    Field management screen
---------------------------------------------------------------------------------------------*/

if ( 'sougorss' == $screen->post_type ) {
?>

<script>
var CFS = CFS || {};
CFS['field_index'] = <?php echo $field_count; ?>;
CFS['field_clone'] = <?php echo json_encode($field_clone); ?>;
CFS['options_html'] = <?php echo json_encode($options_html); ?>;
</script>
<script src="<?php echo CFS_URL; ?>/assets/js/fields.js"></script>
<script src="<?php echo CFS_URL; ?>/assets/js/select2/select2.min.js"></script>
<script src="<?php echo CFS_URL; ?>/assets/js/jquery-powertip/jquery.powertip.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo CFS_URL; ?>/assets/css/fields.css" />
<link rel="stylesheet" type="text/css" href="<?php echo CFS_URL; ?>/assets/js/select2/select2.css" />
<link rel="stylesheet" type="text/css" href="<?php echo CFS_URL; ?>/assets/js/jquery-powertip/jquery.powertip.css" />

<?php
}
