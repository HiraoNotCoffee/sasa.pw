<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Weidner\Goutte\GoutteFacade as GoutteFacade;

class AjaxController extends Controller
{
    function index(Request $request){
        $w = $request["word"];
        $keyWord = urlencode($w);
        $rakuma = $request["rakuma"];
        $yahuoku = $request["yahuoku"];
        $otamart = $request["otamart"];
        $minne = $request["minne"];
        $num = $request["num"];
        $sort = $request["sort"];

        $data = [];

        // ラクマ
        if($rakuma){
            $data = array_merge($data, self::rakuma($keyWord, $num, $sort));
        }

        // ヤフオク
        if($yahuoku){
            $data = array_merge($data, self::yahuoku($keyWord, $num, $sort));
        }

        // オタマート
        if($otamart){
            $wd = str_replace(" ","+", $w);
            $data = array_merge($data, self::otamart($wd, $num, $sort));
        }

        // ミンネ
        if($minne){
            $data = array_merge($data, self::minne($keyWord, $num, $sort));
        }

        // ソート
        foreach( $data as $value) {
            $price_array[] = $value['sort'];
        }
        if($sort == 'high'){
            array_multisort( $price_array, SORT_DESC, SORT_NUMERIC, $data);
        }
        else{
            array_multisort( $price_array, SORT_ASC, SORT_NUMERIC, $data);
        }

        return $data;
    }

    public static function rakuma($keyWord, $num, $sort){
        $param = "";
        switch ($sort) {
            case 'new':
            $param = "&order=desc&sort=created_at";
                break;
            case 'low':
                $param = "&order=asc&sort=sell_price";
                break;
            case 'high':
                $param = "&order=desc&sort=sell_price";
                break;
        }

        $ary = [];
        $goutte = GoutteFacade::request('GET', 'https://fril.jp/search/'. $keyWord. "?transaction=selling". $param);
        $goutte->filter('div.item')->each(function ($section) use (&$ary, &$num) {
            $title = $section->filter('.item-box__item-name')->filter('span')->text();
            $price = "";
            $section->filter('.item-box__item-price')->each(function ($a) use (&$price){
                $a->filter('span')->each(function ($b) use (&$price){
                    if($b->attr('itemprop') == "price"){
                        $price = $b->filter('span')->text();
                    }
                });

            });
            $img = $section->filter('img.img-responsive.lazy')->attr("data-original");
            $link = $section->filter('a')->attr("href");
            $sort = str_replace("," ,"", $price);
            $sort = str_replace("円" ,"", $sort);
            if(count($ary) < $num){
                $ary[] = [
                    "title" => $title,
                    "price" => $price. "円",
                    "img" => $img,
                    "link" => $link,
                    "brand" => "0",
                    "sort" => (int)$sort,
                ];
            }
            else{
                return;
            }
        });

        return $ary;
    }

    public static function yahuoku($keyWord, $num, $sort){
        $param = "";
        switch ($sort) {
            case 'new':
                $param = "&s1=new&o1=d";
                break;
            case 'low':
                $param = "&s1=cbids&o1=a";
                break;
            case 'high':
                $param = "&s1=cbids&o1=d";
                break;
        }

        $ary = [];
        $goutte = GoutteFacade::request('GET', 'https://auctions.yahoo.co.jp/search/search?p='. $keyWord. $param);
        $goutte->filter('.Product')->each(function ($section) use (&$ary, &$num) {
            $title = $section->filter('.Product__titleLink')->text();
            $price = $section->filter('.Product__priceValue.u-textRed')->text();
            $img = $section->filter('img.Product__imageData')->attr("src");
            $link = $section->filter('a.Product__imageLink')->attr("href");
            $sort = str_replace("," ,"", $price);
            $sort = str_replace("円" ,"", $sort);
            if(count($ary) < $num){
                $ary[] = [
                    "title" => $title,
                    "price" => $price,
                    "img" => $img,
                    "link" => $link,
                    "brand" => "1",
                    "sort" => (int)$sort,
                ];
            }
            else{
                return;
            }
        });

        return $ary;
    }

    public static function otamart($keyWord, $num, $sort){
        $ary = [];
        $goutte = GoutteFacade::request('GET', 'https://otamart.com/search/?keyword='. $keyWord);


        if($sort != "low" && $sort != "high"){
                \Log::debug("^^^^^^^");
            $goutte->filter('#main')->each(function ($main) use (&$ary, &$num) {
                $main->filter('li')->each(function ($section) use (&$ary, &$num) {
                    $title;
                    $section->filter('meta')->each(function ($meta) use (&$title) {
                        if($meta->attr('itemprop') == "name"){
                            $title = $meta->attr('content');
                        }
                    });
                    if($title == ""){
                        return;
                    }
                    $price;
                    $section->filter('.price')->filter('span')->each(function ($meta) use (&$price) {
                        if($meta->attr('itemprop') == "price"){
                            $price = $meta->attr('content');
                        }
                    });
                    $img = $section->filter('img')->attr("src");
                    $link = $section->filter('a')->attr("href");
                    $sort = str_replace("," ,"", $price);
                    $sort = str_replace("円" ,"", $sort);

                    if(count($ary) < $num){
                        $ary[] = [
                            "title" => $title,
                            "price" => $price. "円",
                            "img" => $img,
                            "link" => 'https://otamart.com/'. $link,
                            "brand" => "2",
                            "sort" => (int)$sort,
                        ];
                    }
                    else{
                        return;
                    }
                });
            });
        }
        else{
            $flg = 0;
            $pageNum = 0;
            $goutte->filter('.paging')->each(function ($main) use (&$pageNum) {
                $pageNum = $main->filter('a.paging-link.m-plus-light')->text();
            });

            if((int)$pageNum > 10){
                $pageNum = 10;
            }

            for($i = 0;$i < 10;$i ++){
                $rowAry = [];
                $num_ = $i * 36;
                $goutte = GoutteFacade::request('GET', 'https://otamart.com/search/?keyword='. $keyWord. '&start='. $num_);

                $goutte->filter('#main')->each(function ($main) use (&$rowAry) {
                    $main->filter('li')->each(function ($section) use (&$rowAry) {
                        $title;
                        $section->filter('meta')->each(function ($meta) use (&$title) {
                            if($meta->attr('itemprop') == "name"){
                                $title = $meta->attr('content');
                            }
                        });
                        if($title == ""){
                            return;
                        }
                        $price;
                        $section->filter('.price')->filter('span')->each(function ($meta) use (&$price) {
                            if($meta->attr('itemprop') == "price"){
                                $price = $meta->attr('content');
                            }
                        });
                        $img = $section->filter('img')->attr("src");
                        $link = $section->filter('a')->attr("href");
                        $sort_ = str_replace("," ,"", $price);
                        $sort_ = str_replace("円" ,"", $sort_);

                        $rowAry[] = [
                            "title" => $title,
                            "price" => $price. "円",
                            "img" => $img,
                            "link" => 'https://otamart.com/'. $link,
                            "brand" => "2",
                            "sort" => (int)$sort_,
                        ];
                    });
                });
                $ary = array_merge($ary, $rowAry);
            }

            // ソート
            foreach( $ary as $value) {
                $price_array[] = $value['sort'];
            }
            if($sort == 'low'){
                array_multisort( $price_array, SORT_ASC, SORT_NUMERIC, $ary);
            }
            else{
                array_multisort( $price_array, SORT_DESC, SORT_NUMERIC, $ary);
            }

            $ary = array_slice($ary, 0, $num, false);
        }

        return $ary;
    }

    public static function minne($keyWord, $num, $sort){
        $param = "";
        switch ($sort) {
            case 'new':
            $param = "?sort=newer";
                break;
            case 'low':
                $param = "?sort=lower";
                break;
            case 'high':
                $param = "?sort=higher";
                break;
        }

        $ary = [];
        $goutte = GoutteFacade::request('GET', 'https://minne.com/category/saleonly?q='. $keyWord. $param);
        $goutte->filter('.searchItemList.js-search-items')->each(function ($main) use (&$ary, &$num) {
            $main->filter('li')->each(function ($section) use (&$ary, &$num) {
                $title = $section->filter('.item_title')->filter('.js-analytics-click-tracking')->text();
                $price;
                $section->filter('dd.price')->each(function ($meta) use (&$price) {
                    if($meta->filter('span')->attr('class') != "c-productFreeShippingLabel"){
                        $price = $meta->filter('span')->text();
                    }
                });
                $img = $section->filter('img')->attr("data-src");
                $link = $section->filter('a')->attr("href");
                $sort = str_replace("," ,"", $price);
                $sort = str_replace("円" ,"", $sort);
                if(count($ary) < $num){
                    $ary[] = [
                        "title" => $title,
                        "price" => $price,
                        "img" => 'https:'. $img,
                        "link" => "https://minne.com". $link,
                        "brand" => "3",
                        "sort" => (int)$sort,
                    ];
                }
                else{
                    return;
                }
            });
        });

        return $ary;
    }
}
