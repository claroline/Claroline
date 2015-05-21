(function () {
    'use strict';

    var utils = new HeVinci.ObjectiveUtils('users');

    // node expansion
    $(document).on('click', 'table.user-objectives a.expand:not(.disabled)', function (event) {
        event.preventDefault();
        var link = this;
        var row = link.parentNode.parentNode;
        var id = row.dataset.id;
        var type = row.dataset.type;

        if (row.dataset.isLoaded || type === 'competency' || type === 'ability') {
            utils.toggleChildRows(this, false);
        } else {
            // defaults to type === 'user'
            var url = Routing.generate('hevinci_user_objectives', { id: id });
            var childType = 'objective';
            var indent = 1;
            var userId = id; // complementary data only needed for objectives

            if (type === 'objective') {
                userId = $('tr[data-type=user][data-id=' + row.dataset.path + ']').data('id');
                url = Routing.generate('hevinci_load_user_objective_competencies', { id: id, userId: userId });
                childType = 'competency';
                indent = 2;
            }

            $.get(url)
                .done(function (data) {
                    if (type === 'user') {
                        // add the user id to the objectives data (needed for history route generation)
                        data.map(function (objective) {
                            objective.userId = userId;

                            return objective;
                        });
                    }

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
        utils.toggleChildRows(this, true);
    });

    // prevent hash of disabled expansion links to make window scrolling
    $(document).on('click', 'table.user-objectives a.disabled', function (event) {
       event.preventDefault();
    });

    // remove user objective
    $(document).on('click', 'table.user-objectives a.remove', function (event) {
        event.preventDefault();
        utils.removeSubjectObjectiveRow(this, 'user');
    });
})();