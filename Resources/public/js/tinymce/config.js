
function callback_tinymce_init() {
    $('.mce-toolbar').each(function (a, b) {
        if (a > 0) 
            b.setAttribute('style', 'display: none'); 
    });
}
function tinymce_button_ressourceLinker (ed) {
    ed.focus();
    Claroline.ResourceManager.picker('open');

}
function tinymce_button_fullscreenToggle (ed) {
    ed.focus();
    tinyMCE.execCommand('mceFullScreen');
    if( $('.mce-toolbar').last().css('display') === 'none')
    {
        $('.mce-toolbar').each(function (a, b) {
            if (a > 0)
            {
                b.setAttribute('style', 'display: block') 
            } 
        });
    }
    else
    {
        $('.mce-toolbar').each(function (a, b) {
            if (a > 0)
            {
                b.setAttribute('style', 'display: none') 
            } 
        });   
    }
}

