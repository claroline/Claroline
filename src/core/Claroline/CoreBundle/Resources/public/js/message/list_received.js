(function () {

    $('#delete-msg').click(function(){
        var parameters = {};
        var array = new Array();
        var i = 0;

        $('.chk-delete:checked').each(function(index, element){
            array[i] = element.value;
            i++;
        });

        parameters.ids = array;
        var route = Routing.generate('claro_message_delete_to');
        route+='?'+$.param(parameters);

        Claroline.Utilities.ajax({
            url: route,
            success: function(){
                $('.chk-delete:checked').each(function(index, element){
                     $(element).parent().parent().remove();
                })
            }
        });
    });
})();