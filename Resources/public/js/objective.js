(function () {
    'use strict';

    var flasher = new HeVinci.Flasher({ element: $('.panel-body')[0], animate: false });
    var competencyPicker = new HeVinci.CompetencyPicker({
        includeAbilities: false,
        includeLevel: true,
        callback: onCompetencySelection
    });
    var userPicker = new HeVinci.UserPicker({
        title: trans('objective.assign'),
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
            feedback = 'message.objective_association_deleted';
        }

        Claroline.Modal.confirmRequest(
            Routing.generate(url, { id: row.dataset.id }),
            function () {
                removeRow(row);
                flasher.setMessage(trans(feedback));
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
                    insertCompetencyRows(competencies, row);
                    toggleExpandLink(link, true);
                    row.dataset.isLoaded = true;
                })
                .error(function () {
                    Claroline.Modal.error();
                });
        } else {
            toggleChildrenRows(row, link, false);
        }
    });

    // competency collapsing
    $(document).on('click', 'table.objectives a.collapse_', function (event) {
        event.preventDefault();
        var link = this;
        var row = link.parentNode.parentNode;
        toggleChildrenRows(row, link, true);
    });

    // user/group addition
    $(document).on('click', 'table.objectives a.add-users', function (event) {
        event.preventDefault();
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
                insertCompetencyRows([competency], currentObjectiveRow);
                toggleExpandLink($(currentObjectiveRow).find('a.expand').get(0), true)
                flasher.setMessage(trans(message), category);
            })
            .error(function () {
                Claroline.Modal.error();
            });
    }

    function onUserSelection(selection) {
        console.log(selection);
    }

    function trans(message) {
        return Translator.trans(message, {}, 'competency');
    }

    function insertCompetencyRows(competencies, previousSibling) {
        var html = competencies.reduce(function (previousHtml, competency) {
            competency.type = 'competency';
            competency.indent = 1;
            competency.path = previousSibling.dataset.path ?
                (previousSibling.dataset.path + '-' + previousSibling.dataset.id) :
                previousSibling.dataset.id;

            return previousHtml + Twig.render(ObjectiveRow, competency);
        }, '');

        $(html).insertAfter(previousSibling);
    }

    function removeRow(row) {
        // remove children first, if any
        var childrenPath = row.dataset.path ?
            (row.dataset.path + '-' + row.dataset.id) :
            row.dataset.id;
        var childrenSelector = 'tr[data-path^=' + childrenPath + ']';

        $('table.objectives ' + childrenSelector).remove();
        $(row).remove();
    }

    function toggleExpandLink(link, collapse) {
        // "collapse" conflicts with bootstrap..
        $(link).removeClass(collapse ? 'expand disabled' : 'collapse_')
            .addClass(collapse ? 'collapse_' : 'expand')
            .find('i')
            .removeClass(collapse ? 'fa-search-plus disabled': 'fa-search-minus')
            .addClass(collapse ? 'fa-search-minus' : 'fa-search-plus');
    }

    function toggleChildrenRows(parentRow, toggleLink, hide) {
        // "children" rows are identified using a materialized
        // path data attribute (e.g. ancestorId-parentId-...).
        // When expanding a row, only the direct children are shown.
        // When collapsing, all descendants are hidden.

        var childrenPath = parentRow.dataset.path ?
            (parentRow.dataset.path + '-' + parentRow.dataset.id) :
            parentRow.dataset.id;
        var matchType = hide ? '^=' : '=';
        var childrenSelector = 'tr[data-path' + matchType + childrenPath + ']';
        var $tableBody = $(parentRow.parentNode);

        $tableBody.find(childrenSelector)
            .css('display', hide ? 'none' : 'table-row');

        if (hide) {
            $tableBody.find(childrenSelector + '[data-has-children]')
                .find('a.collapse_')
                .removeClass('collapse_')
                .addClass('expand')
                .children('i')
                .removeClass('fa-search-minus')
                .addClass('fa-search-plus');
        }

        toggleExpandLink(toggleLink, !hide);
    }
})();