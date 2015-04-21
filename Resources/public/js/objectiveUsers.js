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

        if (row.dataset.isLoaded) {
            utils.toggleChildRows(row, this, false);
        } else if (type === 'user' ) {
            $.get(Routing.generate('hevinci_user_objectives', {id: id }))
                .done(function (objectives) {
                    utils.insertChildRows(row, objectives, 'objective');
                    utils.toggleExpandLink(link, true);
                    row.dataset.isLoaded = true;
                })
                .error(function () {
                    Claroline.Modal.error();
                });
        } else if (type === 'objective') {
            $.get(Routing.generate('hevinci_load_objective_competencies', {id: id }))
                .done(function (objectives) {
                    utils.insertChildRows(row, objectives, 'competency');
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
})();