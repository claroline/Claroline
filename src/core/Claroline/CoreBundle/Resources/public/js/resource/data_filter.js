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
        var divFiltersString = document.getElementById(buildPrefix+'_active_filters');
        $('#'+buildPrefix+'_select_root').on('change', function(){
            divFiltersString.innerHTML = getActiveFilterString();
        })
        $('#'+buildPrefix+'_select_type').on('change', function(){
            divFiltersString.innerHTML = getActiveFilterString();
        })
        $('#'+buildPrefix+'_date_from').on('change', function(){
            divFiltersString.innerHTML = getActiveFilterString();
        })
        $('#'+buildPrefix+'_date_to').on('change', function(){
            divFiltersString.innerHTML = getActiveFilterString();
        })
    }

    function getActiveFilterString() {
        return $('#'+buildPrefix+'_select_root').val()+','
            +$('#'+buildPrefix+'_select_type').val()+','
            +$('#'+buildPrefix+'_date_from').val()+','
            + $('#'+buildPrefix+'_date_to').val();
    }

    filter.build = function(div, prefix, callBackFilter, callBackReset){
        buildPrefix = prefix;
        buildFilter(div);
        
        $('#'+buildPrefix+'_filter_button').live('click', function(){
            callBackFilter(filter.getActiveFilters());
        })
        $('#'+buildPrefix+'_reset_button').live('click', function(){
            callBackReset();
        })
    }

    filter.getActiveFilters = function(){
        var activeFilters = {};
        activeFilters.root = $('#select_root').val();
        activeFilters.type = $('#select_type').val();
        activeFilters.dateSubmissionTo = $('#rf_date_from').val();
        activeFilters.dateSubmissionFrom = $('#rf_date_to').val();

        return activeFilters;
    }
})();