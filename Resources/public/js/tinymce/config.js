
function callback_tinymce_init() {
    $('.mce-toolbar').each(function (index, element) {
        if (index > 0) 
            element.setAttribute('style', 'display: none'); 
    });

    $('div [aria-label="toggle"] button').html("<i class='icon-resize-full' style='font-family: FontAwesome'></i>");
    $('div [aria-label="Resource Linker"] button').html("<i class='icon-file' style='font-family: FontAwesome'></i>");

    $('body').on('click', 'div [aria-label="toggle"] button', function(){
        if ($(this).parents('.mce-fullscreen').get(0) === undefined) {
            $('i', this).attr('class', 'icon-resize-full');
        } else {
            $('i', this).attr('class', 'icon-resize-small');
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
}

