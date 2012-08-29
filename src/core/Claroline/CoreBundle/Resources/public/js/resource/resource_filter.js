(function () {
    var filter = this.ClaroFilter = {};

    filter.filter = function(
        div,
        prefix,
        callBackToFilter,
        callBackResetFilter
        ){
        filter.toFilter = callBackToFilter;
        filter.resetFilter = callBackResetFilter;
        filter.prefix = prefix;

        buildFilter(div, filter.prefix);

        $('#'+filter.prefix+'-filter-button').live('click', function(){

            var route = createFilterRoute(filter.prefix);
            ClaroUtils.sendRequest(route, function(data){
                filter.toFilter(data)
            })

        });
        $('#'+filter.prefix+'-reset-button').live('click', function(){
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

    function buildFilter(div, buildPrefix){
        ClaroUtils.sendRequest(Routing.generate('claro_resource_renders_filter', {
            prefix: buildPrefix
        }), function(data){
            div.append(data);
            initOnChange(buildPrefix);
        });
    }

    function initOnChange(buildPrefix) {
        var divFiltersString = document.getElementById(buildPrefix+'-active-filters');
        $('#'+buildPrefix+'-select-root').on('change', function(){
            divFiltersString.innerHTML = getActiveFilterString(buildPrefix);
        })
        $('#'+buildPrefix+'-select-type').on('change', function(){
            divFiltersString.innerHTML = getActiveFilterString(buildPrefix);
        })
        $('#'+buildPrefix+'-date-from').on('change', function(){
            divFiltersString.innerHTML = getActiveFilterString(buildPrefix);
        })
        $('#'+buildPrefix+'-date-to').on('change', function(){
            divFiltersString.innerHTML = getActiveFilterString(buildPrefix);
        })
    }

    function getActiveFilterString(buildPrefix) {
        return $('#'+buildPrefix+'-select-root').val()+','
        +$('#'+buildPrefix+'-select-type').val()+','
        +$('#'+buildPrefix+'-date-from').val()+','
        + $('#'+buildPrefix+'-date-to').val();
    }

    function createFilterRoute(buildPrefix) {
        var parameters = {};
        var i = 0;

        var values = $('#'+buildPrefix+'-select-type').val();
        if (values != undefined){
            for(i=0; i< values.length; i++) {
                parameters['types'+i] = values[i];
            }
        }

        values = $('#'+buildPrefix+'-select-root').val();
        if (values != undefined){
            for(i=0; i< values.length; i++) {
                parameters['roots'+i] = values[i];
            }
        }

        if ($('#'+buildPrefix+'-date-from').val()!= '') {
            parameters['dateFrom'] = $('#'+buildPrefix+'-date-from').val();
        }

        if($('#'+buildPrefix+'-date-to').val()!= '') {
            parameters['dateTo'] = $('#'+buildPrefix+'-date-to').val();
        }

        parameters.prefix = buildPrefix;
        return Routing.generate('claro_resource_filter', parameters);
    }
})();