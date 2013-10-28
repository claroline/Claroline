
function callback_tinymce_init() {


    $('.tinymce').each(function(index, element) {
        var parent = $(element).parent().get(0);
        $('.mce-toolbar', parent).each(function (itopbar) {
            if (itopbar > 0)
                $(this).attr('style', 'display: none');
        });

        $('.mce-statusbar', parent).each(function() {
            $(this).attr('style', 'display: none');
        });
    });

    $('div [aria-label="toggle"] button').html("<i class='icon-resize-full' style='font-family: FontAwesome'></i>");
    $('div [aria-label="Resource Linker"] button').html("<i class='icon-file' style='font-family: FontAwesome'></i>");


    $('body').on('click', 'div [aria-label="toggle"] button', function(){
            /** Fullscreen with modal   **/
            if ($(this).parents('.mce-fullscreen').get(0) === undefined) {
                $('i', this).attr('class', 'icon-resize-full');
                // check fake class exists
                ModalMCE = $(this).parents('.nomodal').get(0);

                if (ModalMCE) {
                    // put back modal
                    $(ModalMCE).removeClass('nomodal');
                    $(ModalMCE).addClass('modal');
                }
            } else {
                // check if modal exist => not null
                ModalMCE = $(this).parents('.modal').get(0);

                $('i', this).attr('class', 'icon-resize-small');

                if (ModalMCE) {
                    // if yes take off and add fake class
                    $(ModalMCE).removeClass('modal');
                    $(ModalMCE).addClass('nomodal');
                }
            }
        });
    }

function tinymce_button_ressourceLinker (ed) {
    ed.focus();
    Claroline.ResourceManager.picker('open');
}

function tinymce_button_fullscreenToggle (ed) {
    ed.focus();
    tinyMCE.execCommand('mceFullScreen');
    var lastElementStyle = ($('.mce-toolbar').last().css('display') === 'none' ) ? 'display: block' : 'display: none' ;
    $('.mce-toolbar').each(function (index) {
        if (index > 0 )
            $(this).attr('style', lastElementStyle);
        });

    /* @todo try to add the statusbar */
}

