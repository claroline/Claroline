(function () {
    'use strict';

    window.HeVinci = window.HeVinci || {};

    /**
     * Initializes the listeners for the user(s) objectives page in
     * a given context. Supported contexts are:
     *
     *  - "users"           Admin management page of user objectives
     *  - "myObjectives"    User objectives page
     *
     * @param {String} context
     */
    window.HeVinci.initUserObjectiveUtils = function (context) {
        var routesByContext = {
            users: {
                competencies: 'hevinci_load_user_objective_competencies',
                history: 'hevinci_competency_user_history'
            },
            myObjectives: {
                competencies: 'hevinci_load_my_objective_competencies',
                history: 'hevinci_competency_my_history'
            }
        };

        if (!(context in routesByContext)) {
            throw new Error('Unknown context "' + context + '"');
        }

        var utils = new HeVinci.ObjectiveUtils(context);
        var routes = routesByContext[context];

        // node expansion
        $(document).on('click', 'table.user-objectives a.expand:not(.disabled)', function (event) {
            event.preventDefault();
            var link = this;
            var row = link.parentNode.parentNode;
            var id = row.dataset.id;
            var type = row.dataset.type;
            var childType, indent, url, userId;

            if (row.dataset.isLoaded || type === 'competency' || type === 'ability') {
                utils.toggleChildRows(this, false);
            } else {
                if (type === 'user') {
                    childType = 'objective';
                    indent = 1;
                    userId = id; // not really needed for this type
                    url = Routing.generate('hevinci_user_objectives', {id: id});
                } else if (type === 'objective') {
                    childType = 'competency';
                    indent = 2;
                    userId = row.dataset.path.match(/^(\d+).*$/)[1];
                    url = Routing.generate(routes.competencies, {
                        id: id,
                        userId: userId
                    });
                }

                $.get(url)
                    .done(function (data) {
                        if (type === 'objective') {
                            // add the user id to the competencies data (needed for history route generation)
                            data.map(function (competency) {
                                competency.userId = userId;

                                return competency;
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

        // see user history
        $(document).on('click', 'table.user-objectives a.history:not(.disabled)', function (event) {
            event.preventDefault();
            Claroline.Modal.fromUrl(Routing.generate(routes.history, {
                id: this.dataset.competencyId,
                userId: this.dataset.userId
            }));
        });
    }
})();