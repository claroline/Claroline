(function () {
/*
    var param = 'param';
    function filterCallBack(data, parameter) {
        console.debug(data);
    };
    function resetCallBack(parameter) {
        alert(parameter);
    };

    ClaroFilter.build(
        $('#div-filter'),
        'cr',
        function(data){filterCallBack(data, param)},
        function(){resetCallBack(param)}
    );
*/
    var resourceGetter = new ClaroResourceGetter.getter();
    var interfaceBuilder = new ClaroResourceInterfaceBuilder.builder(
        $('#dr-resources-content'),
        'cr',
        $('#dr-div-form'),
        $('#dr-select-creation'),
        $('#dr-submit-select'),
        $('#dr-download-button'),
        $('#dr-cut-button'),
        $('#dr-copy-button'),
        $('#dr-delete-button'),
        $('#dr-paste-button'),
        $('#dr-close-button'),
        $('#dr-is-flat'),
        resourceGetter
    );

    var builder = interfaceBuilder.getBuilder();
    console.debug(builder);
})();