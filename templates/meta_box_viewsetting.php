<?php global $rss_fields; ?>
表示形式 <a href="#" title="RSSフィードの記事の表示順序です">[?]</a><br />
<label><input type="radio" name="save_sort" value="1" <?php if( $rss_fields->sort == 1) echo 'checked' ?>>登録順</label><br />
<label><input type="radio" name="save_sort" value="2" <?php if( $rss_fields->sort == 2) echo 'checked' ?>>時間順</label><br />
<label><input type="radio" name="save_sort" value="3" <?php if( $rss_fields->sort == 3) echo 'checked' ?>>ランダム</label><br />
<br />
<label><input type="checkbox" name="save_hatebu" value="1" <?php if( $rss_fields->hatebu == 1) echo 'checked' ?>>はてブの追加(未実装)</label><br />
<label><input type="checkbox" name="save_rt" value="1" <?php if( $rss_fields->rt == 1) echo 'checked' ?>>RTの追加(未実装)</label><br />
<br />
日時フォーマット<input name="save_date_format" type="text" size="10" value="<?php echo $rss_fields->date_format; ?>" /><br />
共通アイコン(画像URL)<input name="save_icon" type="text" size="60" value="<?php echo $rss_fields->icon; ?>" /><br />
共通表示コード<br />
<textarea id="commonCode" style="width:80%; height:60px;" name="save_code"><?php echo $rss_fields->code; ?></textarea><br />
変数表<br />
<$title>　記事のタイトル<br />
<$link>　記事のURL<br />
<$icon>　アイコンのURL<br />
<$image>　記事のイメージ　RSSによっては取得できないこともあります<br />
<$datetime>　記事の更新日時<br />
<$site>　記事のサイト名<br />
<$sitelink> 記事のサイトのURL<br />
NGワード表記<br />
<textarea style="width:80%; height:60px;" name="save_ng_word"><?php echo $rss_fields->ng_word; ?></textarea><br />
書き方<br />
1行に1つの設定を入れます<br />
文字列を入れるとそのワードが含まれる場合表示しません<br />
文字列,文字列を入れると,の前の文字列を後ろの文字列に変換します<br />
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
