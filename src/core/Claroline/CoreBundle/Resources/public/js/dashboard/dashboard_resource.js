(function () {

    var param = 'param';
    function filterCallBack(activeFilters, parameter) {
        alert(activeFilters);
        alert(parameter);
    };
    function resetCallBack(parameter) {
        alert(parameter);
    };

    ClaroFilter.build(
        $('#div_filter'),
        'cr',
        function(activeFilters){filterCallBack(activeFilters, param)},
        function(){resetCallBack(param)}
    );

    ClaroResourceManager.init($('#dr_resources_content'), 'cr', $('#dr_resources_back'));
    ClaroResourceManager.rendersThumbnailRoots($('#dr_resources_content'), 'cr');
})();