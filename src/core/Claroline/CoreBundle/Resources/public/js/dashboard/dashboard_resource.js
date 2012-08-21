(function () {

    var param = 'param';
    function filterCallBack(data, parameter) {
        console.debug(data);
    };
    function resetCallBack(parameter) {
        alert(parameter);
    };

    ClaroFilter.build(
        $('#div_filter'),
        'cr',
        function(data){filterCallBack(data, param)},
        function(){resetCallBack(param)}
    );

    ClaroResourceManager.init($('#dr_resources_content'),
        'cr', $('#dr_resources_back'),
        $('#dr_div_form'),
        $('#dr_select_creation'),
        $('#dr_submit_select'),
        $('#dr_download_button'),
        $('#dr_cut_button'),
        $('#dr_copy_button'),
        $('#dr_paste_button'),
        $('#dr_close_button'),
        $('#dr_is_flat')
    );
})();