(function () {
    'use strict';

    var flasher = new HeVinci.Flasher({ element: $('.panel-body')[0], animate: false });

    // objective creation
    $('button#create-objective').on('click', function () {
        window.Claroline.Modal.displayForm(
            Routing.generate('hevinci_new_objective'),
            function (data) {
                $('table#objective-table')
                    .css('display', 'table')
                    .children('tbody')
                    .append(Twig.render(ObjectiveRow, data));
                $('div.alert-info').css('display', 'none');
                flasher.setMessage(trans('message.objective_created'));
            },
            function () {},
            'objective-form'
        );
    });

    function trans(message) {
        return Translator.trans(message, {}, 'competency');
    }
})();