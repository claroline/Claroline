(function () {

    var loading = false;
    var stop = false;
    var mode = 0; //0 = standard || 1 = search

    $('html, body').animate({scrollTop: 0}, 0);
    $('#loading').hide();

    var standardRoute = function(){
        return Routing.generate('claro_admin_paginated_group_list', {
            'format': 'html',
            'offset': $('.row-group').length
        })
    }

    var searchRoute = function(){
        return Routing.generate('claro_admin_paginated_search_group_list', {
            'format': 'html',
            'offset': $('.row-group').length,
            'search': document.getElementById('search-group-txt').value
        })
    }

    lazyloadGroups(standardRoute);

    $(window).scroll(function(){
        if  (($(window).scrollTop()+100 >= $(document).height() - $(window).height()) && loading === false && stop === false){
            if(mode == 0){
                lazyloadGroups(standardRoute);
            } else {
                lazyloadGroups(searchRoute);
            }
        }
    });

    $('.delete-groups-button').click(function(){
        $('#validation-box').modal('show');
        $('#validation-box-body').html(Twig.render(remove_group_confirm, {'nbGroups' : $('.chk-group:checked').length }));
    });

    $('#modal-valid-button').click(function(){
        var parameters = {};
        var i = 0;
        var array = new Array();
        $('.chk-group:checked').each(function(index, element){
            array[i] = element.value;
            i++;
        });
        parameters.id = array;
        var route = Routing.generate('claro_admin_multidelete_group');
        route+= '?'+$.param(parameters);
        ClaroUtils.sendRequest(
            route,
            function(){
                $('.chk-group:checked').each(function(index, element){
                     $(element).parent().parent().remove();
                });
                $('#validation-box').modal('hide');
                $('#validation-box-body').empty();
            },
            undefined,
            'DELETE'
        );
    });

    $('#modal-cancel-button').click(function(){
        $('#validation-box').modal('hide');
        $('#validation-box-body').empty();
    });

    $('#search-group-button').click(function(){
        $('#group-table-body').empty();
        stop = false;
        if (document.getElementById('search-group-txt').value != ''){
            mode = 1;
            lazyloadGroups(searchRoute);
        } else {
            mode = 0;
            lazyloadGroups(standardRoute);
        }
    });

    function lazyloadGroups(route){
        loading = true;
        $('#loading').show();
        ClaroUtils.sendRequest(
            route(),
            function(groups){
                $('#group-table-body').append(groups);
                loading = false;
                $('#loading').hide();
                if (groups.length == 0) {
                    stop = true;
                }
            },
            function(){
                if($(window).height() >= $(document).height() && stop == false){
                    lazyloadGroups(route)
                }
            }
        )
    }
})();