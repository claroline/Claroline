$('.install-pkg').on('click', function(event) {
    event.preventDefault();
    var url = $(event.target).attr('href');

    window.Claroline.Modal.confirmRequest(
        url,
        function() {},
        undefined,
        Translator.trans('confirm_package_install', {}, 'platform'),
        Translator.trans('package_install', {}, 'platform')
    )
});
