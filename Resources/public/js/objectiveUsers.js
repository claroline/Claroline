(function () {
    'use strict';

    var utils = new HeVinci.ObjectiveUtils('users');
    var flasher = new HeVinci.Flasher({ element: $('.panel-body')[0], animate: false });

    // node expansion
    $(document).on('click', 'table.user-objectives a.expand:not(.disabled)', function (event) {
        event.preventDefault();
        var link = this;
        var row = link.parentNode.parentNode;
        var id = row.dataset.id;
        var type = row.dataset.type;

        if (row.dataset.isLoaded || type === 'competency' || type === 'ability') {
            utils.toggleChildRows(row, this, false);
        } else {
            // defaults to type === 'user'
            var route = 'hevinci_user_objectives';
            var childType = 'objective';
            var indent = 1;

            if (type === 'objective') {
                route = 'hevinci_load_objective_competencies';
                childType = 'competency';
                indent = 2;
            }

            $.get(Routing.generate(route, {id: id }))
                .done(function (data) {
                    utils.insertChildRows(row, data, childType, indent);
                    utils.toggleExpandLink(link, true);
                    row.dataset.isLoaded = true;
                })
                .error(function () {
                    Claroline.Modal.error();
                });
        }
    });

    // node collapsing
    $(document).on('click', 'table.user-objectives a.collapse_', function (event) {
        event.preventDefault();
        utils.toggleChildRows(this.parentNode.parentNode, this, true);
    });

    // prevent hash of disabled expansion links to make window scrolling
    $(document).on('click', 'table.user-objectives a.disabled', function (event) {
       event.preventDefault();
    });

    // remove user objective
    $(document).on('click', 'table.user-objectives a.remove', function (event) {
        event.preventDefault();
        var row = this.parentNode.parentNode;
        var url = Routing.generate('hevinci_remove_user_objective', {
            objectiveId: row.dataset.id,
            userId: row.dataset.path.match(/^(\d+)\-*/)[1] // user id is the root in the node path
        });
        Claroline.Modal.confirmRequest(
            url,
            function () {
                utils.removeRow(row);
                flasher.setMessage(utils.trans('message.objective_unassigned'));
            },
            null,
            utils.trans('message.objective_unassign_user_confirm'),
            utils.trans('objective.unassign_user')
        );
    });
})();