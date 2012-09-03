(function () {
    var resourceGetter = new ClaroResourceGetter.getter(resource_thumbnail_template, resource_list_template);
    var resourceFilter = new ClaroFilter.filter($('#div-filter'));

    var interfaceBuilder = new ClaroResourceInterfaceBuilder.builder(
        $('#dr-resources-content'),
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
})();