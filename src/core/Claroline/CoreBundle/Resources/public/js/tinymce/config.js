tinymce.init({
    selector: 'textarea',
    theme: 'modern',
    plugins: [
        'advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker',
        'searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking',
        'save table contextmenu directionality emoticons template paste textcolor'
    ],
    content_css: $('link.maincss').attr('href'),
    toolbar: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist   | link image |  preview media fullpage |   emoticons',
    style_formats: [
        {title: 'warning', block: 'div', classes: 'alert'},
        {title: 'error', block: 'p', classes: 'alert alert-error'},
        {title: 'title 1', block: 'h1'},
        {title: 'title 2',  block: 'h2'},
        {title: 'title 3', block: 'h3'},
        {title: 'Table styles', block: 'div', classes : 'well'},
        {title: 'Table row 1', selector: 'tr', classes: 'tablerow1'}
    ],
    formats: {
        alignleft : {selector : 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes : 'text-left'},
        aligncenter : {selector : 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes : 'text-center'},
        alignright : {selector : 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes : 'text-right'}
    }
});
