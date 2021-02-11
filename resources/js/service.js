$(function() {
    console.log("aa");
    var main = function() {
        var word = $('#target').val()
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
        // $('#result').html('<img class="loading" src="/wp-content/themes/keni8-child/images/loading.svg">')
        console.log("click");
        $.ajaxSetup({
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
        });
        $.ajax({
            url: "/scraping",
            word: word,
            rakuma: rakuma,
            yahuoku: yahuoku,
            otamart: otamart,
            minne: minne,
            type: 'post',
            dataType: 'json',
            cache: false
        }).done(function(data) {
            console.log(data);
            // console.log("ok");
            $('#result').html($(data).find('h2').text());
        }).fail(function(XMLHttpRequest, textStatus, errorThrown) {
            console.log("error");
        })
    }

    $('.btn-search').on('click', main);

    $('#target').keypress(function(e) {
        if (e.which == 13) {
            console.log("key" + e.which);
            main();
            return false;
        };
    });

});
