(function () {
    'use strict';

    window.HeVinci = window.HeVinci || {};
    window.HeVinci.ObjectiveUtils = Utils;

    /**
     * Initializes the helper for a given context. Supported contexts are:
     *
     *  - "objectives"  Admin management page of objectives
     *  - "users"       Admin management page of user objectives
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
            }
        };

        if (!context in availableContexts) {
            throw new Error('Unknown context "' + context + '"');
        }

        this.context = availableContexts[context];
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
     * @param {HTMLTableRowElement} parentRow
     * @param {HTMLAnchorElement}   toggleLink
     * @param {Boolean}             hide
     */
    Utils.prototype.toggleChildRows = function (parentRow, toggleLink, hide) {
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

        this.toggleExpandLink(toggleLink, !hide);
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