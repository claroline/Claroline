function editionAv() {
    //"use strict";

    var popup = false;
    var row;
    $('.button_editionA').live('click', function () {
        row = $(this).parents('tr.ligne_choice:first');
        if (row.find('#divReplaceTextarea').length) {
            //var text0 = $("#divReplaceTextarea").html();
            var text0 = row.find('#divReplaceTextarea').html();
            //$("#input_popup").val(text0);
            $('#input_popup').val(text0);
        }
        else {
            var text1 = row.contents('td:nth-child(2)').find('textarea').val();
            $('#input_popup').val(text1);
        }

        if (popup === false) {
            $('#overlayEffect').fadeIn('slow');
            $('#popupContainer').fadeIn('slow');
            $('#close').fadeIn('slow');
            popup = true;
        }

    });

    //////////

    $('#close').click(function () {
        var text = $('#input_popup').val();
        row.contents('td:nth-child(1)').find('textarea').val(text);
        row.contents('td:nth-child(1)').find('textarea').hide();
        row.find('#divReplaceTextarea').remove();
        row.contents('td:nth-child(1)').find('br').remove();
        row.contents('td:nth-child(1)').append('<br /><div id="divReplaceTextarea" style="border:solid 1px red; width:200px; height:110px; padding:5px; overflow:auto; "></div> ');
        row.contents('td:nth-child(1)').children('div').last().html(text);
        $('#input_popup').val('');
        hidePopup();
    });

    $('#overlayEffect').click(function () {
        hidePopup();
    });

    function hidePopup() {
        //"use strict";

        if (popup === true) {
            $('#overlayEffect').fadeOut('slow');
            $('#popupContainer').fadeOut('slow');
            $('#close').fadeOut('slow');
            popup = false;
        }
    }

    ///////////

    $('#overlayEffect').css({
        'display': 'none',
        'position': 'fixed',
        'opacity': '0.7',
        'height': '100%',
        'width': '100%',
        'top': '0',
        'left': '0',
        'background': '-moz-linear-gradient(rgba(11,11,11,0.1), rgba(11,11,11,0.6)) repeat-x rgba(11,11,11,0.2)',
        'background': '-webkit-gradient(linear, 0% 0%, 0% 100%, from(rgba(11,11,11,0.1)), to(rgba(11,11,11,0.6))) repeat-x rgba(11,11,11,0.2)',
        'z-index': '1'
    });


    $('#popupContainer').css({
        'position': 'fixed',
        'left': '30%',
        'top': '25%',
        'width': '650px',
        'border': '5px solid #cecece',
        'z-index': '2',
        'padding': '10px',
        'border': '1px solid rgba(33, 33, 33, 0.6)',
        '-moz-box-shadow': '0 0 2px rgba(255, 255, 255, 0.6) inset',
        '-webkit-box-shadow': '0 0 2px rgba(255, 255, 255, 0.6) inset',
        'box-shadow': '0 0 2px rgba(255, 255, 255, 0.6) inset'
    });

    $('#close').css({
        'cursor': 'pointer',
        'width': '50px',
        'height': '26px',
        'position': 'fixed',
        'z-index': '3200',
        'position': 'absolute',
        'top': '-25px',
        'right': '-22px'
    });

    $('.hiddenCat').css({
        'display': 'none'
    });
}