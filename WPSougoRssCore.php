<?php
/**
 * Created by IntelliJ IDEA.
 * User: user
 * Date: 2015/06/25
 * Time: 18:35
 */

class WPSougoRssCore {
    private $settingData;

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

    public function inset(){
        include_once(ABSPATH . WPINC . '/rss.php');

        $ng_words = $this->settingData->ng_word;
        $ng_words = explode(PHP_EOL,$ng_words);
        $ngs = array();
        $replaces = array();
        foreach ($ng_words as $ng_word){
            if (preg_match('/(.*),(.*)/',$ng_word,$match)){
                $replaces[] = array(trim($match[1]),trim($match[2]));
            } else{
                if (trim($ng_word) != "") {
                    $ngs[] = trim($ng_word);
                }
            }
        }

        $i = 0;
        $instanceItems = array();
        $items = array();
        foreach ($this->settingData->rssFieldOnes[0] as $fieldData) {
            /* @var $fieldData SR_RssFieldOne */
            $rss_parameter = $this->get_external_rss($fieldData->url, $fieldData->start);
            if ($rss_parameter !== null) {
                $rssItems = $rss_parameter['items'];
                $siteName = $rss_parameter['siteName'];
                $siteLink = $rss_parameter['siteLink'];

                $j = 0;
                foreach ($rssItems as $rssItem) {
                    foreach ($ngs as $ng) {
                        if (mb_strpos($rssItem->get_title(), $ng) !== false) {
                            continue 2;
                        }
                    }
                    if ($j < $fieldData->start){
                        $j++;
                        continue;
                    } else if ($fieldData->start + $fieldData->count - 1 < $j){
                        break;
                    }
                    preg_match('/<img([ ]+)([^>]*)src\=["|\']([^"|^\']+)["|\']([^>]*)>/', $rssItem->get_content(), $matches);
                    if (isset($matches[0])) {
                        if (preg_match("/counter2/", $matches[3])) {
                            $image = "";
                        } else {
                            $image = $matches[3];
                        }
                    } else {
                        $image = "";
                    }
                    if($fieldData->common == true) {
                        $code = $this->settingData->code;
                    } else {
                        $code = $fieldData->code;
                    }
                    if($fieldData->iconCommon == true) {
                        $icon = $this->settingData->icon;
                    } else {
                        $icon = $fieldData->icon;
                    }
                    $items[] = array(
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
            } else if($fieldData->url == 'instance') {
                $instanceItems[] = array(
                    'code' => $fieldData->code,
                    'number' => $i,
                    'layout' => false,
                    'ng_check' => false,
                );
                $i++;
            } else if($fieldData->url == 'layout') {
                $instanceItems[] = array(
                    'code' => $fieldData->code,
                    'number' => $i,
                    'layout' => true,
                    'ng_check' => false,
                );
                $i++;
            }
        }
        if ($this->settingData->sort == 2) {
            usort($items, array($this, "sort_time"));
            foreach($instanceItems as $value){
                array_splice($items,$value['number'],0,array(array('code' => $value['code'])));
            }
        } else if($this->settingData->sort == 3){
            foreach($instanceItems as $value){
                if ($value['layout'] == false) {
                    $items[] = array('code' => $value['code']);
                }
            }
            shuffle($items);
            foreach($instanceItems as $value){
                if ($value['layout'] == true) {
                    array_splice($items, $value['number'], 0, array(array('code' => $value['code'])));
                }
            }
        } else{
            foreach($instanceItems as $value){
                array_splice($items,$value['number'],0,array(array('code' => $value['code'])));
            }
        }
        $blocks = array();
        foreach ($items as $item) {
            if ($item['ng_check'] === true) {
                foreach ($replaces as $replace) {
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