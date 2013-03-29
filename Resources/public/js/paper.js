function show_hint(idHint, path_hint_show, confirm_hint, nbr_hint, paper) {
    if (confirm(confirm_hint)) {
        $.ajax({
            type: "POST",
            url: path_hint_show,
            data: {
                id: idHint,
                paper: paper
            }, 
            cache: false,
            success: function (data){
                $('#div_hint'+nbr_hint).html(data);
            }                   
        });  
    } else {
        // return fales;
    }
}