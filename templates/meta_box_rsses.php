<?php
global $rss_fields;
?>
<div>
    <?php
    if ($is_edit == true){
        echo '<input type="hidden" name="save_ID" value="' . $rss_fields->ID . '" />';
    }
    ?>
    <div id="rss-field"></div>
    <input type="button" onclick="addField()" value="追加" />
</div>

<?php
echo '<script type="text/javascript">';
foreach($rss_fields->rssFieldOnes[0] as $rssField){
    echo "addField('".$rssField->url."','".$rssField->icon."','".$rssField->start."','".$rssField->count."','".$rssField->code."','".$rssField->common."','".$rssField->iconCommon."');";
}
echo '</script>';
?>