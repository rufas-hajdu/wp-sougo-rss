<div>
    <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
        <input type="hidden" name="wp_sougo_rss_submit" value="write">
        <?php
        if ($is_edit == true){
            echo '<input type="hidden" name="save_ID" value="'.$rssFields->ID.'" />';
        }
        ?>
        <div style="margin:0 0 20px 0;">
            title<input type="text" name="save_title" value="<?php echo $rssFields->title; ?>">
        </div>
        <div id="rss-field"></div>
        <input type="button" onclick="addField()" value="追加" />
    </form>
</div>