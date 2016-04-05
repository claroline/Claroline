(function () {
    'use strict';

    var utils = new HeVinci.ObjectiveUtils('objectives');
    var competencyPicker = new HeVinci.CompetencyPicker({
        includeAbilities: false,
        includeLevel: true,
        callback: onCompetencySelection
    });
    var userPicker = new HeVinci.UserPicker({
        title: utils.trans('objective.assign'),
        callback: onUserSelection
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
                utils.setFlashMessage('message.objective_created');
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
                utils.setFlashMessage('message.objective_edited');
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
            feedback = 'message.objective_association_deleted';
        }

        Claroline.Modal.confirmRequest(
            Routing.generate(url, { id: row.dataset.id }),
            function () {
                utils.removeRow(row);
                utils.setFlashMessage(feedback);
            },
            null,
            utils.trans(message),
            utils.trans(title)
        );
    });

    // competency association
    $(document).on('click', 'table.objectives a.associate', function (event) {
        event.preventDefault();
        currentObjectiveRow = this.parentNode.parentNode;
        currentObjectiveId = currentObjectiveRow.dataset.id;
        competencyPicker.open();
    });

    // prevent hash of disabled expansion links to make window scrolling
    $(document).on('click', 'table.objectives a.disabled', function (event) {
       event.preventDefault();
    });

    // competency expansion
    $(document).on('click', 'table.objectives a.expand:not(.disabled)', function (event) {
        event.preventDefault();
        var link = this;
        var row = link.parentNode.parentNode;

        if (row.dataset.type === 'objective'
            && row.dataset.hasOwnProperty('hasChildren')
            && !row.dataset.isLoaded) {
            $.get(Routing.generate('hevinci_load_objective_competencies', {id: row.dataset.id}))
                .done(function (competencies) {
                    utils.insertChildRows(row, competencies, 'competency');
                    utils.toggleExpandLink(link, true);
                    row.dataset.isLoaded = true;
                })
                .error(function () {
                    Claroline.Modal.error();
                });
        } else {
            utils.toggleChildRows(link, false);
        }
    });

    // competency collapsing
    $(document).on('click', 'table.objectives a.collapse_', function (event) {
        event.preventDefault();
        var link = this;
        var row = link.parentNode.parentNode;
        utils.toggleChildRows(link, true);
    });

    // user/group addition
    $(document).on('click', 'table.objectives a.add-users', function (event) {
        event.preventDefault();
        currentObjectiveRow = this.parentNode.parentNode;
        currentObjectiveId = currentObjectiveRow.dataset.id;
        userPicker.open();
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

                competencyPicker.close();
                utils.insertChildRows(currentObjectiveRow, [competency], 'competency');
                utils.toggleExpandLink($(currentObjectiveRow).find('a.expand').get(0), true)
                utils.setFlashMessage(message, category);
            })
            .error(function () {
                Claroline.Modal.error();
            });
    }

    function onUserSelection(selection) {
        var route = 'hevinci_objectives_assign_to_' + selection.target;
        var params = {};

        params['objectiveId'] = currentObjectiveId;
        params[selection.target + 'Id'] = selection.id;

        $.post(Routing.generate(route, params))
            .done(function (data, statusText, xhr) {
                var message = 'message.objective_assigned';
                var category = 'success';

                if (xhr.status === 204) {
                    message = 'message.objective_already_assigned_to_' + selection.target;
                    category = 'warning';
                }

                userPicker.close();
                utils.setFlashMessage(message, category);
            })
            .error(function () {
                Claroline.Modal.error();
            });
    }
})();