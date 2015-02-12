(function () {
    var flasher = new HeVinci.Flasher({ element: $('.panel-body')[0] });

    $('button#create-scale').on('click', function () {
        window.Claroline.Modal.displayForm(
            Routing.generate('hevinci_new_scale'),
            function () {
                flasher.setMessage(Translator.trans('message.scale_created', {}, 'competency'));
                $('button#create-scale').css('display', 'none');
                $('a#manage-scales').css('display', 'inline-block');
                $('span#status-info').html(Translator.trans('info.no_frame', {}, 'competency'));
            },
            function() {},
            'scale-form'
        );
    });
})();