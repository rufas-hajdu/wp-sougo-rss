<?php
/**
 * Created by IntelliJ IDEA.
 * User: user
 * Date: 2015/06/25
 * Time: 18:35
 */

include_once 'SR_OsrData.php';

class WPSougoRssCore {
    private $settingData;
    private $post_id;
    const POST_META_KEY = 'osrdata';
    const CACHE_TIME = 600;

    private function sort_time($a, $b){
        $cmp = -strcmp($a['date'], $b['date']);
        return $cmp;
    }

    private function getSavedRssFields($id) {
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
        wp_reset_postdata();
        return $saved_setting;
    }

    function __construct($id) {
        $this->settingData = $this->getSavedRssFields($id);
        $this->post_id = $id;
    }

    private function get_external_rss($url){
        $rss = fetch_feed($url);
        if (!is_wp_error($rss)){
            $rss->set_cache_duration(600);
            $rss->init();
            $maxItems = $rss->get_item_quantity(0);
            $items = $rss->get_items(0, $maxItems);
            return array(
                'items' => $items,
                'siteName' => $rss->get_title(),
                'siteLink' => $rss->get_permalink(),
            );
        } else {
            return null;
        }
    }

    /**
     * OsrDataにあるsavedを元に期限切れかどうかを判断する
     *
     * @param SR_OsrData $osrData
     * @return bool
     */
    private function is_expired_osrdata($osrData){
        $time = time();
        if (isset($osrData->saved)) {
            $gotTime = $osrData->saved;
        } else {
            $gotTime = null;
        }
        if ($gotTime === null || ($time - $gotTime >= self::CACHE_TIME)){
            return false;
        }
        return true;
    }

    /**
     * OsrDataを保存する
     *
     * @param SR_OsrData $osrData
     */
    private function saveOsrData(SR_OsrData $osrData) {
        $osrData->saved = time(); // キャッシュ判定のため現在時刻を保管しておく
        update_post_meta($this->post_id, self::POST_META_KEY, $osrData);
    }

    /**
     * get_osrdata
     * outSiteRssData => OsrData (外部RSSデータ、略してオズールデータ)
     * 外部サイトのRSSを整形したもの、オズールデータを作成したりキャッシュから取り出したりする関数
     *
     * @return SR_OsrData
     */
    private function get_osrdata() {
        $osrData = get_post_meta($this->post_id, self::POST_META_KEY, true);
        if ($this->is_expired_osrdata($osrData)) {
            return $osrData;
        } else {
            $osrData = new SR_OsrData();

            $ng_words = $this->settingData->ng_word;
            $ng_words = explode(PHP_EOL,$ng_words);
            $ngs = array();
            foreach ($ng_words as $ng_word){
                if (preg_match('/(.*),(.*)/',$ng_word,$match)){
                    $osrData->replaces[] = array(trim($match[1]),trim($match[2]));
                } else{
                    if (trim($ng_word) != "") {
                        $ngs[] = trim($ng_word);
                    }
                }
            }

            $i = 0;
            foreach ($this->settingData->rssFieldOnes[0] as $fieldData) {
                /* @var $fieldData SR_RssFieldOne */
                $rss_parameter = $this->get_external_rss($fieldData->url, $fieldData->start);
                if ($rss_parameter !== null) {
                    $rssItems = $rss_parameter['items'];
                    $siteName = $rss_parameter['siteName'];
                    $siteLink = $rss_parameter['siteLink'];

                    $j = 0; // 一つの外部サイトのRSS記事のインデックス
                    foreach ($rssItems as $rssItem) {
                        foreach ($ngs as $ng) {
                            if (mb_strpos($rssItem->get_title(), $ng) !== false) {
                                continue 2;
                            }
                        }
                        if ($j < $fieldData->start) {
                            $j++;
                            continue;
                        } else if ($fieldData->start + $fieldData->count - 1 < $j) {
                            break;
                        }
                        if (preg_match('/<img([ ]+)([^>]*)src\=["|\']([^"|^\']+)["|\']([^>]*)>/', $rssItem->get_content(), $matches)) {
                            // RSSの記事の本文に画像のURLがあった場合
                            if (preg_match("/counter2/", $matches[3])) { // Livedoorは必ずこの形式が出現するので省く
                                $image = "";
                            } else {
                                $image = $matches[3];
                            }
                        } else {
                            $image = "";
                        }

                        if ($fieldData->common == true) { // 共通コードを利用する
                            $code = $this->settingData->code;
                        } else {
                            $code = $fieldData->code;
                        }
                        if ($fieldData->iconCommon == true) { // 共通アイコンを利用する
                            $icon = $this->settingData->icon;
                        } else {
                            $icon = $fieldData->icon;
                        }
                        $osrData->items[] = array(
                            'link' => $rssItem->get_permalink(),
                            'title' => $rssItem->get_title(),
                            'date' => $rssItem->get_date('YmdHis'),
                            'datetime' => $rssItem->get_date($this->settingData->date_format),
                            'image' => $image,
                            'code' => $code,
                            'icon' => $icon,
                            'site' => $siteName,
                            'sitelink' => $siteLink,
                            'ng_check' => true,
                        );
                        $i++;
                        $j++;
                    }
                } else if ($fieldData->url == 'instance') {
                    $osrData->instanceItems[] = array(
                        'code' => $fieldData->code,
                        'number' => $i,
                        'layout' => false,
                        'ng_check' => false,
                    );
                    $i++;
                } else if ($fieldData->url == 'layout') {
                    $osrData->instanceItems[] = array(
                        'code' => $fieldData->code,
                        'number' => $i,
                        'layout' => true,
                        'ng_check' => false,
                    );
                    $i++;
                }
            }
        }
        $this->saveOsrData($osrData);
        return $osrData;
    }

    public function inset(){
        include_once(ABSPATH . WPINC . '/rss.php');

        $osrData = $this->get_osrdata();


        if ($this->settingData->sort == 2) { // オプションの表示形式が時間順
            usort($osrData->items, array($this, "sort_time"));
            foreach($osrData->instanceItems as $value){
                array_splice($osrData->items,$value['number'],0,array(array('code' => $value['code'])));
            }
        } else if($this->settingData->sort == 3){ // オプションの表示形式がランダム
            foreach($osrData->instanceItems as $value){
                if ($value['layout'] == false) {
                    $osrData->items[] = array('code' => $value['code']);
                }
            }
            shuffle($osrData->items);
            foreach($osrData->instanceItems as $value){
                if ($value['layout'] == true) {
                    array_splice($osrData->items, $value['number'], 0, array(array('code' => $value['code'])));
                }
            }
        } else{ // 表示形式が登録順だった場合にはレイアウトなど順序を保持させるため
            foreach($osrData->instanceItems as $value){
                array_splice($osrData->items,$value['number'],0,array(array('code' => $value['code'])));
            }
        }
        $blocks = array();
        foreach ($osrData->items as $item) {
            if ($item['ng_check'] === true) {
                foreach ((array)$osrData->replaces as $replace) {
                    $item['title'] = str_replace($replace[0], $replace[1], $item['title']);
                }
            }
            $block = $item['code'];
            $block = str_replace('<$link>',$item['link'],$block);
            $block = str_replace('<$title>',$item['title'],$block);
            $block = str_replace('<$icon>',$item['icon'],$block);
            $block = str_replace('<$image>',$item['image'],$block);
            $block = str_replace('<$datetime>',$item['datetime'],$block);
            $block = str_replace('<$site>',$item['site'],$block);
            $block = str_replace('<$sitelink>',$item['sitelink'],$block);
            $blocks[] = $block;
        }
        return implode($blocks,'');
    }
}