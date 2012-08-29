(function () {
    var resourceGetter = new ClaroResourceGetter.getter();
    var resourceFilter = new ClaroFilter.filter($('#div-filter'),'cr');

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
        resourceGetter,
        resourceFilter
    );

    var builder = interfaceBuilder.getBuilder();
    console.debug(builder);
})();