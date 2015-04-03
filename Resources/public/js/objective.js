(function () {
    'use strict';

    var flasher = new HeVinci.Flasher({ element: $('.panel-body')[0], animate: false });
    var picker = new HeVinci.CompetencyPicker({
        includeAbilities: false,
        includeLevel: true,
        callback: onCompetencySelection
    });
    var currentObjectiveRow = null;
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

    // objective or competency association deletion
    $(document).on('click', 'table.objectives a.remove', function (event) {
        event.preventDefault();
        var row = this.parentNode.parentNode;
        var url = 'hevinci_delete_objective';
        var title = 'objective.delete';
        var message = 'message.objective_deletion_confirm';
        var feedback = 'message.objective_deleted';

        if (row.dataset.type === 'competency') {
            url = 'hevinci_delete_objective_association';
            title = 'competency.delete';
            message = 'message.objective_association_deletion_confirm';
            var feedback = 'message.objective_association_deleted';
        }

        Claroline.Modal.confirmRequest(
            Routing.generate(url, { id: row.dataset.id }),
            function () {
                $(row).remove();
                flasher.setMessage(trans('message.objective_deleted'));
            },
            null,
            trans(message),
            trans(title)
        );
    });

    // competency association
    $(document).on('click', 'table.objectives a.associate', function (event) {
        event.preventDefault();
        currentObjectiveRow = this.parentNode.parentNode;
        currentObjectiveId = currentObjective.dataset.id;
        picker.open();
    });

    // competency expansion
    $(document).on('click', 'table.objectives a.expand', function (event) {
        event.preventDefault();
        var row = this.parentNode.parentNode;

        if (row.dataset.type === 'objective' && row.dataset.hasOwnProperty('hasChildren')) {
            if (!row.dataset.isLoaded) {
                $.get(Routing.generate('hevinci_load_objective_competencies', {id: row.dataset.id}))
                    .done(function (competencies) {
                        insertCompetencyRows(competencies, row);
                        // toggle minus icon
                    })
                    .error(function () {
                        Claroline.Modal.error();
                    });
            } else {
                // display child rows, toggle minus icon
            }
        }
    });

    function onCompetencySelection(selection) {
        var url;

        if (selection.targetType !== 'competency') {
            throw new Error('Target is not a competency');
        }

        url = Routing.generate('hevinci_objective_link_competency', {
            id: currentObjectiveId,
            competencyId: selection.targetId,
            levelId: selection.levelId
        });

        $.post(url)
            .done(function (competency, statusText, xhr) {
                var message = 'message.objective_competency_associated';
                var category = 'success';

                if (xhr.status === 204) {
                    message = 'message.objective_competency_already_associated';
                    category = 'warning';
                }

                picker.close();
                insertCompetencyRows([competency], currentObjectiveRow);
                flasher.setMessage(trans(message), category);
            })
            .error(function () {
                Claroline.Modal.error();
            });
    }

    function trans(message) {
        return Translator.trans(message, {}, 'competency');
    }

    function insertCompetencyRows(competencies, previousSibling) {
        var html = competencies.reduce(function (previousHtml, competency) {
            competency.type = 'competency';
            competency.indent = 1;

            return previousHtml + Twig.render(ObjectiveRow, competency);
        }, '');

        $(html).insertAfter(previousSibling);
    }
})();