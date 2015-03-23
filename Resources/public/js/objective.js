(function () {
    'use strict';

    var flasher = new HeVinci.Flasher({ element: $('.panel-body')[0], animate: false });

    // objective creation
    $('button#create-objective').on('click', function () {
        window.Claroline.Modal.displayForm(
            Routing.generate('hevinci_new_objective'),
            function (data) {
                flasher.setMessage(trans('message.objective_created'));
                console.log(data)
            },
            function () {},
            'objective-form'
        );
    });

    function trans(message) {
        return Translator.trans(message, {}, 'competency');
    }
})();