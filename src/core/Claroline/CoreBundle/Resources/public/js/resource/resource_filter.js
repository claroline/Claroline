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
            initOnChange(div);
        });
    }

    function initOnChange(div) {
        var divFiltersString = $('.active-filters', div).first();

        $('.select-root', div).first().on('change', function(){
            divFiltersString.html(getActiveFilterString(div));
        })
        $('.select-type', div).first().on('change', function(){
            divFiltersString.html(getActiveFilterString(div));
        })
        $('.date-from', div).first().on('change', function(){
            divFiltersString.html(getActiveFilterString(div));
        })
        $('.date-to', div).first().on('change', function(){
            divFiltersString.html(getActiveFilterString(div));
        })
    }

    function getActiveFilterString(div) {
        return $('.select-root', div).first().val()+','
        +$('.select-type', div).first().val()+','
        +$('.date-from', div).first().val()+','
        +$('.date-to', div).val();
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

        if ($('.date-from', div).first().val()!= '') {
            parameters['dateFrom'] = $('.date-from', div).first().val();
        }

        if($('.date-to', div).first().val()!= '') {
            parameters['dateTo'] = $('.date-to').first().val();
        }

        return Routing.generate('claro_resource_filter', parameters);
    }
})();