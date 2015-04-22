(function () {
    'use strict';

    window.HeVinci = window.HeVinci || {};
    window.HeVinci.ObjectiveUtils = Utils;

    /**
     * Initializes the helper for a given context. Supported contexts are:
     *
     *  - "objectives"  Admin management page of objectives
     *  - "users"       Admin management page of user objectives
     *  - "groups"      Admin management page of group objectives
     *
     * @param {String} context
     * @constructor
     */
    function Utils(context) {
        var availableContexts = {
            objectives: {
                rowTemplate: ObjectiveRow
            },
            users: {
                rowTemplate: UserObjectiveRow
            },
            groups: {
                rowTemplate: GroupObjectiveRow
            }
        };

        if (!context in availableContexts) {
            throw new Error('Unknown context "' + context + '"');
        }

        this.context = availableContexts[context];
        this.flasher = new HeVinci.Flasher({ element: $('.panel-body')[0], animate: false });
    }

    /**
     * Insert multiple rows as "children" of a given row.
     *
     * @param {HTMLTableRowElement} parentRow
     * @param {Array}               data
     * @param {String}              type
     * @param {Number}              [indent]
     */
    Utils.prototype.insertChildRows = function (parentRow, data, type, indent) {
        var self = this;
        var html = data.reduce(function (previousHtml, item) {
            item.type = type;
            item.indent = indent || 1;
            item.path = parentRow.dataset.path ?
                (parentRow.dataset.path + '-' + parentRow.dataset.id) :
                parentRow.dataset.id;

            return previousHtml + Twig.render(self.context.rowTemplate, item);
        }, '');

        $(html).insertAfter(parentRow);
    };

    /**
     * Removes a row and all its child rows.
     *
     * @param {HTMLTableRowElement} row
     */
    Utils.prototype.removeRow = function (row) {
        // remove children first, if any
        var childrenPath = row.dataset.path ?
            (row.dataset.path + '-' + row.dataset.id) :
            row.dataset.id;
        var childrenSelector = 'tr[data-path^=' + childrenPath + ']';

        $(row.parentNode).find(childrenSelector).remove();
        $(row).remove();
    };

    /**
     * Changes the status of an expansion link.
     *
     * @param {HTMLAnchorElement}   link
     * @param {Boolean}             collapse
     */
    Utils.prototype.toggleExpandLink = function (link, collapse) {
        // "collapse" conflicts with bootstrap..
        $(link).removeClass(collapse ? 'expand disabled' : 'collapse_')
            .addClass(collapse ? 'collapse_' : 'expand')
            .find('i')
            .removeClass(collapse ? 'fa-search-plus disabled': 'fa-search-minus')
            .addClass(collapse ? 'fa-search-minus' : 'fa-search-plus');
    };

    /**
     * Shows or hides rows which are the "children" of a given row.
     *
     * @param {HTMLAnchorElement}   parentToggleLink
     * @param {Boolean}             hide
     */
    Utils.prototype.toggleChildRows = function (parentToggleLink, hide) {
        // "children" rows are identified using a materialized
        // path data attribute (e.g. ancestorId-parentId-...).
        // When expanding a row, only the direct children are shown.
        // When collapsing, all descendants are hidden.

        var parentRow = parentToggleLink.parentNode.parentNode;
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

        this.toggleExpandLink(parentToggleLink, !hide);
    };

    /**
     * Removes an objective row which is a child of a user or a child row.
     *
     * @param {HTMLAnchorElement}   removeLink
     * @param {String}              target      ("user"|"group")
     */
    Utils.prototype.removeSubjectObjectiveRow = function (removeLink, target) {
        if (target !== 'user' && target !== 'group') {
            throw new Error('Invalid target');
        }

        var self = this;
        var row = removeLink.parentNode.parentNode;
        var route = 'hevinci_remove_' + target + '_objective';
        var params = {};

        params['objectiveId'] = row.dataset.id;
        params[target + 'Id'] = row.dataset.path.match(/^(\d+)\-*/)[1]; // target id is the root in the node path

        Claroline.Modal.confirmRequest(
            Routing.generate(route, params),
            function () {
                self.removeRow(row);
                self.setFlashMessage('message.objective_unassigned');
            },
            null,
            self.trans('message.objective_unassign_' + target + '_confirm'),
            self.trans('objective.unassign_' + target)
        );
    };

    /**
     * Makes a flash message appear.
     *
     * @param {String}  message
     * @param {String}  [category]
     */
    Utils.prototype.setFlashMessage = function (message, category) {
        this.flasher.setMessage(this.trans(message), category);
    };

    /**
     * Translates a string.
     *
     * @param message
     * @returns {String}
     */
    Utils.prototype.trans = function (message) {
        return Translator.trans(message, {}, 'competency');
    }
})();