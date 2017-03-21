/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

(function () {
    'use strict';

    window.Claroline = window.Claroline || {};
    var simpleRights = window.Claroline.SimpleRights = {};

    /**
     * Get true or false if a checkbox is checked.
     *
     * @param element The checkbox html element
     *
     * @return boolean
     */
    simpleRights.getValue = function (element)
    {
        return $(element).prop('checked') ? true : false;
    };

    /**
     * Get a parent element of a checkbox by his Id
     *
     * @param id The id of the parent element
     * @param element The checkbox html element
     *
     * @return
     */
    simpleRights.get = function (id, element)
    {
        return $(element).parents('#rights-form-resource-tab-content').first().find('#' + id).first();
    };

    /**
     * Get the html elemnt of simple table parameters.
     *
     * @param element A checkbox html element
     *
     * @return html elemnt of simple table parameters
     */
    simpleRights.getTable = function (element)
    {
        return simpleRights.get('simple', element);
    };

    /**
     * Get the html elemnt of general table parameters.
     *
     * @param element A checkbox html element
     *
     * @return html elemnt of general table parameters
     */
    simpleRights.getGeneral = function (element)
    {
        return simpleRights.get('general', element);
    };

    /**
     * This method check 'everyone' checkbox if all the other checkbox are checked in simple table parameters,
     *
     * @param element The checkbox html element
     *
     * @return
     */
    simpleRights.checkEveryone = function (element)
    {
        if (!simpleRights.getValue(element)) {
            $('input#everyone', simpleRights.getTable(element)).prop('checked', false);
        }

        if (
            simpleRights.getValue($('input#workspace', simpleRights.getTable(element))) &&
            simpleRights.getValue($('input#platform', simpleRights.getTable(element)))
        ) {
            $('input#everyone', simpleRights.getTable(element)).prop('checked', true);
        }
    };

    /**
     * This method check the rights in general parameters for a given role (simple parameter role).
     *
     * @param role The selector of the html element for a simple parameter checkbox
     * @param element The checkbox html element
     */
    simpleRights.checkRole = function (role, element)
    {
        var mask = simpleRights.getMask(element);
        var general = $('tr.' + role, simpleRights.getGeneral(element));

        if (simpleRights.getValue(element)) {
            general.each(function () {
                for (var key in mask) {
                    if (mask.hasOwnProperty(key)) {
                        $($(this).find('input').get(parseInt(key) + 1)).prop('checked', mask[key]);
                    }
                }
            });
        } else {
            general.each(function () {
                $(this).find('input').prop('checked', false);
            });
        }
    };

    /**
     * This method check simple checkbox paramaters when general parameters changes for a given role.
     *
     * @param selector The selector of the html element for a general parameter group of checkbox (tr element)
     * @param role the selector of html element for a simple parameter checkbox
     * @param element The checkbox html element
     *
     * @return
     */
    simpleRights.checkGeneral = function (selector, role, element) {
        var mask = simpleRights.getMask(element);

        $('tr.' + selector, simpleRights.getGeneral(element)).each(function () {
            var value = true;

            for (var key in mask) {
                if (mask.hasOwnProperty(key) && mask[key] &&
                    mask[key] !== simpleRights.getValue($(this).find('input').get(parseInt(key) + 1))
                ) {
                    value = false;
                }
            }

            $('input#' + role, simpleRights.getTable(element)).prop('checked', value);
        });
    };

    /**
     * This method use checkGeneral in order to check all rights in simple parameters when general parameters
     * changes or when the page is loaded.
     *
     * @param element The checkbox html element
     */
    simpleRights.checkAll = function (element)
    {
        simpleRights.checkGeneral('role.anonymous', 'anonymous', element);
        simpleRights.checkGeneral('role:not(.anonymous,.user)', 'workspace', element);
        simpleRights.checkGeneral('role.user', 'platform', element);
        simpleRights.checkEveryone(element);
        //recursive option
        var value = $('#advanced-recursive-option').prop('checked');
        $('#recursive-option').prop('checked', value);
    };

    /**
     * Get the mask for a general parameters role.
     *
     * @TODO Get real backend mask
     */
    simpleRights.getMask = function (element) {
        var json = $(simpleRights.get('mask', element)).html();

        if (json !== undefined && json !== '') {
            return $.parseJSON(json);
        } else {
            return [true, false, true, false, false];
        }
    };

    /**
     * Trigger when click in 'everyone' checkbox.
     *
     * @param element The checkbox html element
     */
    simpleRights.everyone = function (element) {
        $('input', simpleRights.getTable(element)).prop('checked', simpleRights.getValue(element));
        simpleRights.anonymous(element);
        simpleRights.workspace(element);
        simpleRights.platform(element);
    };

    /**
     * Trigger when click in 'anonymous' checkbox.
     *
     * @param element The checkbox html element
     */
    simpleRights.anonymous = function (element) {
        simpleRights.checkEveryone(element);
        simpleRights.checkRole('role.anonymous', element);
    };

    /**
     * Trigger when click in 'workspace' checkbox.
     *
     * @param element The checkbox html element
     */
    simpleRights.workspace = function (element) {
        simpleRights.checkEveryone(element);
        simpleRights.checkRole('role:not(.anonymous,.user)', element);
    };

    /**
     * Trigger when click in 'platform' checkbox.
     *
     * @param element The checkbox html element
     */
    simpleRights.platform = function (element) {
        simpleRights.checkEveryone(element);
        simpleRights.checkRole('role.user', element);
    };

    /**
     * Trigger when click in 'recursive' checkbox.
     *
     * @param element The checkbox html element
     */
    simpleRights.recursive = function (element) {
        var value = $(element).prop('checked');
        $('#advanced-recursive-option').prop('checked', value);
    };
})();
