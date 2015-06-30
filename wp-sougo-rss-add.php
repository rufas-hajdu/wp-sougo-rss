<?php
require_once('SR_RssField.php');
require_once('SR_RssFieldOne.php');
//ini_set('display_errors', true);

/**
 *  POSTのデータを整形してRssFieldの形に直す
 */
function storeRssFields(){
    $rssField = new SR_RssField();
    //$rssField->ID;
    foreach ($_POST['save_field'] as $postdata) { // 0, 1, 2, 3, 4, ...
        $rssFieldOne = new SR_RssFliedOne();
        foreach ($rssFieldOne as $key => $value) { // url, icon, start, ...
            $rssFieldOne->$key = $postdata[$key];
        }
        $rssField->rssFieldOnes[] = $rssFieldOne;
    }
    foreach ($rssField as $key => $value) {

        switch ($key) {
            case 'rssFieldOnes':
                break;
            default:
                $rssField->$key = @$_POST['save_'.$key];
                break;
        }
    }
    storeRssField($rssField);
}

/**
 * 実際にデータベースに保管するところ
 */
function storeRssField(SR_RssField $rssField) {
    global $current_user; /** @var WP_User */
    $data = array(
        'post_name'    => $rssField->title,			// スラッグ
        'post_author'  =>  $current_user->ID,			// 投稿者のID
        'post_date'    => date_i18n('Y-m-d H:i:s') ,	// 投稿時刻
        'post_type'    => 'sougorss' ,			// 投稿タイプ（カスタム投稿タイプも指定できるよ）
        'post_status'  => 'publish' ,		// publish (公開) とか、draft (下書き) とか
        'post_title'   => $rssField->title,		// 投稿のタイトル
        'post_content' => '' ,			// 投稿の本文
        //'post_category'=> array(1, 2) ,		// カテゴリーID を配列で
        //'post_tags'    => array('タグ1', 'タグ2') ,	// 投稿のタグを配列で
    );
    if (is_numeric($rssField->ID)) { // update
        $posts = new wp_post_helper($rssField->ID); // get post data;
        $posts->set($data);
        $ID = $posts->update();
    } else { // insert
        $posts = new wp_post_helper($data);
        $ID = $posts->insert();
    }
    foreach ($rssField as $key => $value) { // upate custom fields
        switch ($key) {
            case 'ID':
            case 'title':
                break;
            default:;
                delete_post_meta($ID,$key);
                add_post_meta($ID, $key, $value);
                break;
        }
    }
}

/**
 * @return SR_Rssfield[]
 */
function getSavedRssFields($id) {
    global $post;
    $args = array(
        'post_status' => 'publish',
        'post_type' => 'sougorss',
        'posts_per_page' => -1,
        'p' => $id,
    );
    $saved_setting = new SR_RssField;
    $query = new WP_Query($args);
    while($query->have_posts()) {
        $query->the_post();
        foreach($saved_setting as $key => $value){
            switch ($key){
                case 'ID':
                    $saved_setting->$key = $id;
                    break;
                case 'title':
                    $saved_setting->$key = get_the_title();
                    break;
                case 'rssFieldOnes':
                    $saved_setting->$key = get_post_meta($post->ID,$key,false);
                    break;
                default:
                    $saved_setting->$key = get_post_meta($post->ID,$key,true);
                    break;
            }
        }
    }
    return $saved_setting;
}


if (isset($_POST['wp_sougo_rss_submit'])){
    storeRssFields();
}
?>

<script type="text/javascript">
    var fields = new Array();
    var fieldArray = function (url, icon, start, count, code, common, iconCommon){
        this.url = url || '';
        this.icon = icon || '';
        this.start = start || '';
        this.count = count || '';
        this.code = code || '';
        this.common = common? 'checked' : '';
        this.iconCommon = iconCommon? 'checked' : '';
    }
    function addFieldDOM(number){
        var rss_field = document.getElementById('rss-field');
        var field = document.createElement('div');
        field.id = 'field'+ number;
        field.style.margin = '0 0 20px 0';
        field.innerHTML = 'url<input name=save_field['+ number +'][url] type="text" size="60" value="'+ fields[number].url +'"/><br />' +
        'icon(画像URL)<input name=save_field['+ number +'][icon] type="text" size="60" value="'+ fields[number].icon +'"/><br />' +
        'start<input name=save_field['+ number +'][start] type="text" size="5" value="'+ fields[number].start +'"/><br />' +
        'count<input name=save_field['+ number +'][count] type="text" size="5" value="'+ fields[number].count +'"/><br />' +
        'code<br /><textarea style="width:80%; height:60px;" name=save_field['+ number +'][code]>'+ fields[number].code +'</textarea><br />' +
        '<label style="margin-right:20px;"><input type="checkbox" name=save_field['+ number +'][common] value="true" '+ fields[number].common +'>共通code</label>' +
        '<label><input type="checkbox" name=save_field['+ number +'][iconCommon] value="true" '+ fields[number].iconCommon +'>共通icon</label><br />' +
        '<input type="button" onclick="fieldUp(\''+ number +'\')" value="上へ" />' +
        '<input type="button" onclick="fieldDown(\''+ number +'\')" value="下へ" />' +
        '<input type="button" onclick="deleteField(\''+ number +'\')" value="削除" />';
        rss_field.appendChild(field);
    }
    function deleteFieldDOM (){
        var rss_field = document.getElementById('rss-field');
        rss_field.innerHTML = '';
    }
    function tmpField(){
        for(i = 0;i < fields.length; i++){
            fields[i].url = document.getElementsByName('save_field['+ i +'][url]')[0].value;
            fields[i].icon = document.getElementsByName('save_field['+ i +'][icon]')[0].value;
            fields[i].start = document.getElementsByName('save_field['+ i +'][start]')[0].value;
            fields[i].count = document.getElementsByName('save_field['+ i +'][count]')[0].value;
            fields[i].code = document.getElementsByName('save_field['+ i +'][code]')[0].value;
            fields[i].common = document.getElementsByName('save_field['+ i +'][common]')[0].checked ? 'checked' : '' ;
            fields[i].iconCommon = document.getElementsByName('save_field['+ i +'][iconCommon]')[0].checked ? 'checked' : '' ;
        }
    }
    function refreshFieldDOM(){
        deleteFieldDOM();
        for(i = 0;i < fields.length; i++){
            addFieldDOM(i);
        }
    }
    function addField(url, icon, start, count, code, common, iconCommon, addNumber) {
        addNumber = isNaN(addNumber)? false : addNumber;
        tmpField();
        if (addNumber === false) {
            fields.push(new fieldArray(url, icon, start, count, code, common, iconCommon));
        } else{
            fields.splice(parseInt(addNumber), 0, new fieldArray(url, icon, start, count, code, common, iconCommon));
        }
        refreshFieldDOM();
    }
    function deleteField (number){
        tmpField();
        fields.splice(parseInt(number),1);
        refreshFieldDOM();
    }
    function fieldUp(number){
        if (number != 0) {
            tmpField();
            var remove = fields.splice(parseInt(number), 1);
            fields.splice(parseInt(number) - 1, 0, remove[0]);
            refreshFieldDOM();
        }
    }
    function fieldDown(number){
        tmpField();
        var remove = fields.splice(parseInt(number),1);
        fields.splice(parseInt(number) + 1,0,remove[0]);
        refreshFieldDOM();
    }
    function changeLivedoorFormat(){
        tmpField();
        for(i = 0; i < fields.length; i++){
            fields[i].common = 'checked';
        }
        addField('layout', '上部分（一番上に設置）　※消さないでください ライブドア変更用タグです', '', '', '<div class="blogroll-channel"><ul class="blogroll-list-wrap">', '', '',0);
        addField('layout', '下部分（一番下に設置）　※消さないでください ライブドア変更用タグです', '', '', '</ul></div>', '', '', false);
        var Element = document.getElementById('commonCode');
        Element.innerHTML = '<li class="blogroll-list"><a title="<$title>" class="blogroll-link" href="<$link>" target="_blank"><$title></a></li>';
    }
    function changeLivedoorIconFormat(){
        tmpField();
        for(i = 0; i < fields.length; i++){
            fields[i].common = 'checked';
            fields[i].iconCommon = 'checked'
        }
        addField('layout', '上部分（一番上に設置）　※消さないでください ライブドア変更用タグです', '', '', '<div class="blogroll-channel"><ul class="blogroll-list-wrap">', '', '',0);
        addField('layout', '下部分（一番下に設置）　※消さないでください ライブドア変更用タグです', '', '', '</ul></div>', '', '', false);
        var Element = document.getElementById('commonCode');
        Element.innerHTML = '<li class="blogroll-list"><img class="blogroll-icon" src="<$icon>"><a title="<$title>" class="blogroll-link" href="<$link>" target="_blank"><$title></a></li>';
    }
</script>
<?php if (!empty($_GET['id'])) {
    $rssFields = getSavedRssFields($_GET['id']);
    $is_edit = true;
} else {
    $rssFields = new SR_RssField();
}
?>
<h3>WP相互RSS 追加</h3>
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
        <div id="rss-field">
        </div>
        <input type="button" onclick="addField()" value="追加" /><br />
        <br />
        表示形式<br />
        <label><input type="radio" name="save_sort" value="1" <?php if($rssFields->sort == 1) echo 'checked' ?>>登録順</label><br />
        <label><input type="radio" name="save_sort" value="2" <?php if($rssFields->sort == 2) echo 'checked' ?>>時間順</label><br />
        <label><input type="radio" name="save_sort" value="3" <?php if($rssFields->sort == 3) echo 'checked' ?>>ランダム</label><br />
        <br />
        <label><input type="checkbox" name="save_hatebu" value="1" <?php if($rssFields->hatebu == 1) echo 'checked' ?>>はてブの追加(未実装)</label><br />
        <label><input type="checkbox" name="save_rt" value="1" <?php if($rssFields->rt == 1) echo 'checked' ?>>RTの追加(未実装)</label><br />
        <br />
        日時フォーマット<input name="save_date_format" type="text" size="10" value="<?php echo $rssFields->date_format; ?>" /><br />
        共通アイコン(画像URL)<input name="save_icon" type="text" size="60" value="<?php echo $rssFields->icon; ?>" /><br />
        共通表示コード<br />
        <textarea id="commonCode" style="width:80%; height:60px;" name="save_code"><?php echo $rssFields->code; ?></textarea><br />
        変数表<br />
        <$title>　記事のタイトル<br />
        <$link>　記事のURL<br />
        <$icon>　アイコンのURL<br />
        <$image>　記事のイメージ　RSSによっては取得できないこともあります<br />
        <$datetime>　記事の更新日時<br />
        <$site>　記事のサイト名<br />
        <$sitelink> 記事のサイトのURL<br />
        <div style="margin:20px 0;">
            ライブドア変更用<br />
            各フィールドのurl,start,countを設定後1回だけ下のボタンを押してください<br />
            urlにlayoutという一番上と一番下に要素ができますがライブドア相互RSS用に作られますので消さないでください<br />
            またすべての要素が共通コードになりますのでinstance以外は共通コードが適用されます<br />
            適用後に追加する際にはlayoutの一番下の要素より上に入れてください<br />
            ライブドアアイコン付き変更に変更する場合や再度ライブドア変更ボタンを押す際は<br />
            一番上と一番下のlayout要素を消した上で変更してください<br />
            <input type="button" onclick="changeLivedoorFormat()" value="変換" /><br />
            <br />
            ライブドアアイコン付き変更用<br />
            各フィールドのurl,start,count　共通アイコン 設定後1回だけ下のボタンを押してください<br />
            urlにlayoutという一番上と一番下に要素ができますがライブドア相互RSS用に作られますので消さないでください<br />
            またすべての要素が共通コードと共通アイコンになりますのでinstance以外は共通コードが適用されます<br />
            適用後に追加する際にはlayoutの一番下の要素より上に入れてください<br />
            ライブドア変更に変更に変更する場合や再度ライブドアアイコン付き変更ボタンを押す際は<br />
            一番上と一番下のlayout要素を消した上で変更してください<br />
            <input type="button" onclick="changeLivedoorIconFormat()" value="アイコン変換" /><br />
        </div>
        <input name="wp_sougo_rss_submit" type="submit" value="save" />
    </form>
</div>
<?php
echo '<script type="text/javascript">';
foreach($rssFields->rssFieldOnes[0] as $rssField){
    echo "addField('".$rssField->url."','".$rssField->icon."','".$rssField->start."','".$rssField->count."','".$rssField->code."','".$rssField->common."','".$rssField->iconCommon."');";
}
echo '</script>';
?>