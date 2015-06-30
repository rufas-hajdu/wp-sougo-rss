<?php
global $rssFields;
?>
<div>
    <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
        <input type="hidden" name="wp_sougo_rss_submit" value="write">
        <?php
        if ($is_edit == true){
            echo '<input type="hidden" name="save_ID" value="'.$rssFields->ID.'" />';
        }
        ?>
        <div id="rss-field"></div>
        <input type="button" onclick="addField()" value="追加" />
    </form>
</div>

<?php
echo '<script type="text/javascript">';
foreach($rssFields->rssFieldOnes[0] as $rssField){
    echo "addField('".$rssField->url."','".$rssField->icon."','".$rssField->start."','".$rssField->count."','".$rssField->code."','".$rssField->common."','".$rssField->iconCommon."');";
}
echo '</script>';
?>

