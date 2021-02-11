@extends('layouts.app')

@section('js')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="js/service.js"></script>

<script type="text/javascript">
$(function() {
    console.log("aa");

    function main() {
        var word = $('#target').val()
        var num = $('#num').val()
        var sort_ = $('#sort_').val()
        if(num == ""){
            alert('１サイトから何件とってくるのさ');
            return ;
        }
        var rakuma = 0;
        var yahuoku = 0;
        var otamart = 0;
        var minne = 0;
        if($('#rakuma').prop('checked')){
            rakuma = 1;
        }
        if($('#yahuoku').prop('checked')){
            yahuoku = 1;
        }
        if($('#otamart').prop('checked')){
            otamart = 1;
        }
        if($('#minne').prop('checked')){
            minne = 1;
        }
        $('#result').html('検索中...');
        let hoge = setTimeout(function(){
            $('#result').html('検索中... もう少し待って');
        },1500)
        let hoges = setTimeout(function(){
            $('#result').html('検索中... もう少し待って オタマート時間かかるんよな...');
        },2500)
        $.ajaxSetup({
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
        });
        $.ajax({
            // url: "/scraping",
            url: "{{ action("AjaxController@index") }}",
            data:{
                word: word,
                sort: sort_,
                rakuma: rakuma,
                yahuoku: yahuoku,
                otamart: otamart,
                minne: minne,
                num: num,
            },
            type: 'post',
            dataType: 'json',
            cache: false
        }).done(function(data) {
            console.log(data);
            let max = data.length;
            let html = "";
            for(let i = 0;i < max;i ++){
                let row = '<div class="box_row">' +
                    '<img src="' + data[i]['img'] + '" alt="" class="img">' +
                    '<div class="box">' +
                        '<p class="title">' + data[i]['title'] + '</p>' +
                        '<p class="price">' + data[i]['price'] + '</p>' +
                        '<p class="brand">' + brand[data[i]['brand']] + '</p>' +
                        '<a href="' + data[i]['link'] + '" class="link" target="_blank">商品を確認する</a>' +
                    '</div>' +
                '</div>';

                html += row;
            }

            clearTimeout(hoge);
            clearTimeout(hoges);
            $('#result').html(html);
        }).fail(function(XMLHttpRequest, textStatus, errorThrown) {
            alert("ごめん、エラー起きたわ開発者に連絡して欲しいtwitter @hirao");
        })
    }

    $('.btn-search').on('click', function(){
        if($('#target').val() != ""){
            main();
        }
        else{
            alert('検索ワードを入力して')
        }
    });

    $('#target').keypress(function(e) {
        if (e.which == 13) {
            console.log("key" + e.which);
            main();
            return false;
        };
    });
});

</script>
@endsection

@section('content')
<script type="text/javascript">
    const brand = {!!json_encode($ary, JSON_UNESCAPED_UNICODE)!!};
</script>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8" style="margin-bottom:32px;">
            <div class="card">
                <div class="card-header">
                    色々なサービスを比較
                </div>

                <div class="card-body">
                    <div id="sort">
                        <label for="rakuma">
                            <input type="checkbox" checked name="" value="true" id="rakuma">
                            ラクマ
                        </label>
                        <label for="yahuoku">
                            <input type="checkbox" checked name="" value="true" id="yahuoku">
                            ヤフオク
                        </label>
                        <label for="otamart">
                            <input type="checkbox" checked name="" value="true" id="otamart">
                            オタマート
                        </label>
                        <label for="minne">
                            <input type="checkbox" checked name="" value="true" id="minne">
                            ミンネ
                        </label>
                    </div>
                    <div>
                        <label for="num">
                            １サイトから検索する件数
                            <input type="number" name="" value="3" id="num">
                        </label>
                        <label for="sort_">
                            <select class="" name="" id="sort_">
                                <option value="">並び替え</option>
                                <option value="low">価格の低い順</option>
                                <option value="high">価格の高い順</option>
                                <option value="new">新しい順</option>
                            </select>
                        </label>
                    </div>
                    <input id="target" class="target" type="text" placeholder="検索ワードを入力" value="" name="kw">
                    <button class="btn-search">
                      <img src="https://lets-hack.tech/wp-content/themes/keni80_wp_standard_all/images/icon/search_black.svg" width="18" height="18">
                      <noscript>
                        <img src="https://lets-hack.tech/wp-content/themes/keni80_wp_standard_all/images/icon/search_black.svg" width="18" height="18">
                      </noscript>
                    </button>
                    <p>※メルカリは規約的にダメだった</p>
                </div>
            </div>
        </div>
    </div>


    <div class="row justify-content-center">

        <div id="result" class="col-md-8">
            <h3>ニュース</h3>
            <ul>
                <li>2020/1/15  サービス公開</li>
                <li>2020/1/16  並び替えられるように機能を拡張</li>
            </ul>
            <p>何か欲しい機能が合ったら<a href="https://twitter.com/hirqo" target="_blank">@hirao</a>まで</p>
        </div>
    </div>
</div>
@endsection
