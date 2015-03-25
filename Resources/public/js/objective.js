(function () {
    'use strict';

    var flasher = new HeVinci.Flasher({ element: $('.panel-body')[0], animate: false });
    var picker = new HeVinci.CompetencyPicker({
        includeAbilities: false,
        includeLevel: true,
        callback: onCompetencySelection
    });
    var currentObjectiveId = null;

    // objective creation
    $('button#create-objective').on('click', function () {
        Claroline.Modal.displayForm(
            Routing.generate('hevinci_new_objective'),
            function (data) {
                $('table.objectives')
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

    // objective deletion
    $(document).on('click', 'table.objectives a.remove', function (event) {
        event.preventDefault();
        var row = this.parentNode.parentNode;
        var url = row.dataset.type === 'objective' ?
            'hevinci_delete_objective' :
            'hevinci_remove_objective_association';
        Claroline.Modal.confirmRequest(
            Routing.generate(url, { id: row.dataset.id }),
            function () {
                $(row).remove();
                flasher.setMessage(trans('message.objective_deleted'));
            },
            null,
            trans('message.objective_deletion_confirm'),
            trans('objective.delete')
        );
    });

    // objective edition
    $(document).on('click', 'table.objectives a.edit', function (event) {
        event.preventDefault();
        var row = this.parentNode.parentNode;
        Claroline.Modal.displayForm(
            Routing.generate('hevinci_objective_edit_form', { id: row.dataset.id }),
            function (data) {
                $(row).replaceWith(Twig.render(ObjectiveRow, data));
                flasher.setMessage(trans('message.objective_edited'));
            },
            function () {},
            'objective-form'
        );
    });

    // competency association
    $(document).on('click', 'table.objectives a.associate', function (event) {
        event.preventDefault();
        currentObjectiveId = this.parentNode.parentNode.dataset.id;
        picker.open();
    });

    function onCompetencySelection(selection) {
        var url;

        if (selection.targetType !== 'competency') {
            throw new Error('Target is not a competency');
        }

        url = Routing.generate('hevinci_objective_link_competency', {
            id: currentObjectiveId,
            competencyId: selection.targetId,
            levelId: selection.levelId,
        });

        $.post(url)
            .done(function (data) {
                picker.close();
                flasher.setMessage(trans('message.objective_competency_associated'));
            })
            .error(function () {
                Claroline.Modal.error();
            });
    }

    function trans(message) {
        return Translator.trans(message, {}, 'competency');
    }
})();