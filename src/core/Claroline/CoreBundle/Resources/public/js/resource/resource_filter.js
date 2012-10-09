(function () {
    var filter = this.ClaroFilter = {};

    filter.filter = function(
        div,
        callBackToFilter,
        callBackResetFilter
        ){
        filter.toFilter = callBackToFilter;
        filter.resetFilter = callBackResetFilter;

        buildFilter(div);

        $('.filter-button', div).live('click', function(){
            var route = createFilterRoute(div);
            ClaroUtils.sendRequest(route, function(data){
                filter.toFilter(data)
            })

        });

        $('.reset-button', div).live('click', function(){
            filter.resetFilter();
        });

        return {
            setCallBackToFilter: function(callBack){
                filter.toFilter = callBack;
            },
            setCallResetFilter: function(callBack){
                filter.resetFilter = callBack;
            }
        }
    }

    function buildFilter(div){
        ClaroUtils.sendRequest(Routing.generate('claro_resource_renders_filter'), function(data){
            div.append(data);
            $('.select-mime-type', div).first().hide();
            initOnChange(div);
        });
    }

    function initOnChange(div) {
        var divFiltersString = $('.active-filters', div).first();

        $('.select-root', div).first().on('change', function(){
            divFiltersString.html(getActiveFilterString(div));
        })

        $('.select-type', div).first().on('change', function(){
            if(getActiveFilterString(div).indexOf('file')!=-1){
                $('.select-mime-type', div).first().show();
            } else {
                $('.select-mime-type', div).first().hide();
                $('.select-mime-type', div).first().val([]);
            }
            divFiltersString.html(getActiveFilterString(div));
        })
        $('.date-from', div).first().on('change', function(){
            divFiltersString.html(getActiveFilterString(div));
        })
        $('.date-to', div).first().on('change', function(){
            divFiltersString.html(getActiveFilterString(div));
        })
        $('.field-res-name', div).first().on('change', function(){
            divFiltersString.html(getActiveFilterString(div));
        })

        $('.select-mime-type', div).first().on('change', function(){
            divFiltersString.html(getActiveFilterString(div));
        })
    }

    function getActiveFilterString(div) {
        return $('.select-root', div).first().val()+','
        +$('.select-type', div).first().val()+','
        +$('.date-from', div).first().val()+',\n'
        +$('.date-to', div).val()+','
        +$('.field-res-name', div).val()+','
        +$('.select-mime-type', div).val();
    }

    function createFilterRoute(div) {
        var parameters = {};
        var i = 0;

        var values = $('.select-type', div).first().val();
        if (values != undefined){
            for(i=0; i< values.length; i++) {
                parameters['types'+i] = values[i];
            }
        }

        values = $('.select-root', div).first().val();
        if (values != undefined){
            for(i=0; i< values.length; i++) {
                parameters['roots'+i] = values[i];
            }
        }

        values = $('.select-mime-type', div).first().val();
        if (values != undefined){
            for(i=0; i< values.length; i++) {
                parameters['mimeTypes'+i] = values[i];
            }
        }

        if ($('.date-from', div).first().val()!= '') {
            parameters['dateFrom'] = $('.date-from', div).first().val();
        }

        if($('.date-to', div).first().val()!= '') {
            parameters['dateTo'] = $('.date-to').first().val();
        }

        if($('.field-res-name', div).first().val()!= '') {
            parameters['name'] = $('.field-res-name').first().val();
        }

        return Routing.generate('claro_resource_filter', parameters);
    }
})();