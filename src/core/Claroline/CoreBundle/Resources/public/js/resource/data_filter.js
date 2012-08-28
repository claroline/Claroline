(function () {
    var filter = this.ClaroFilter = {};

    /* private attributes */
    var buildPrefix = 'default';

    function buildFilter(div){
        ClaroUtils.sendRequest(Routing.generate('claro_resource_renders_filter', {prefix: buildPrefix}), function(data){
            div.append(data);
            initOnChange();
        });
    }

    function initOnChange() {
        var divFiltersString = document.getElementById(buildPrefix+'-active-filters');
        $('#'+buildPrefix+'-select-root').on('change', function(){
            divFiltersString.innerHTML = getActiveFilterString();
        })
        $('#'+buildPrefix+'-select-type').on('change', function(){
            divFiltersString.innerHTML = getActiveFilterString();
        })
        $('#'+buildPrefix+'-date-from').on('change', function(){
            divFiltersString.innerHTML = getActiveFilterString();
        })
        $('#'+buildPrefix+'-date-to').on('change', function(){
            divFiltersString.innerHTML = getActiveFilterString();
        })
    }

    function getActiveFilterString() {
        return $('#'+buildPrefix+'-select-root').val()+','
            +$('#'+buildPrefix+'-select-type').val()+','
            +$('#'+buildPrefix+'-date-from').val()+','
            + $('#'+buildPrefix+'-date-to').val();
    }

    function createFilterRoute() {
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

    filter.build = function(div, prefix, callBackFilter, callBackReset){
        buildPrefix = prefix;
        buildFilter(div);

        $('#'+buildPrefix+'-filter-button').live('click', function(){

            var route = createFilterRoute();
            ClaroUtils.sendRequest(route, function(data){
                callBackFilter(data);
            })
        //            callBackFilter(filter.getActiveFilters());
        })
        $('#'+buildPrefix+'-reset-button').live('click', function(){
            callBackReset();
        })
    }

    filter.getActiveFilters = function(){
        var activeFilters = {};
        activeFilters.root = $('#select-root').val();
        activeFilters.type = $('#select-type').val();
        activeFilters.dateSubmissionTo = $('#rf-date-from').val();
        activeFilters.dateSubmissionFrom = $('#rf-date-to').val();

        return activeFilters;
    }
})();