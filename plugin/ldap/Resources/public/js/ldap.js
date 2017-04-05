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
    window.Claroline.LDAP = {
        'entries': null,
        'userName': null,
        'firstName': null,
        'lastName': null,
        'email': null,
        'code': null,
        'locale': null,
        'groupName': null,
        'groupCode': null
    };

    var routing = window.Routing;
    var translator = window.Translator;
    var modal = window.Claroline.Modal;
    var common = window.Claroline.Common;
    var ldap = window.Claroline.LDAP;

    /**
     * Set the value of an ldap object attribute.
     *
     * @param attribute The name of ldap object attribute
     * @param value The new value of the attribute
     */
    ldap.setAttribute = function (attribute, value)
    {
        ldap[attribute] = value;
    };

    /**
     * Return a list of LDAP object attributes
     */
    ldap.getAttributes = function (type) {
        if (type === 'users') {
            return ['userName', 'firstName', 'lastName', 'email', 'code', 'locale'];
        }

        return ['groupName', 'groupCode'];
    };

    /**
     * This method fill select with a list of LDAP subObjectClass (after choose the father LDAP objecClass).
     */
    ldap.fillSelect = function ()
    {
        var idx = ldap.getEntryIndex();
        if (idx !== null) {
            var select = $(document.createElement('select'));
            select.append($(document.createElement('option')));

            for (var i = 0; i < ldap.entries[idx].count; i++) {
                select.append($(document.createElement('option')).html(ldap.entries[idx][i]));
            }

            $('#ldapAttributes').removeClass('hide');
            $('#ldapAttributes select').html(select.html());
        }
    };

    /**
     * Reset LDAP object and hide select, preview and the footer.
     */
    ldap.reset = function ()
    {
        $('#ldapAttributes, #ldapPreview, #ldapFooter').addClass('hide');
        $('#ldapPreview td').html('');

        var type = $('#ldapObjectClass').data('type');
        var attributes = ldap.getAttributes(type);
        for (var attribute in attributes) {
            if (attributes.hasOwnProperty(attribute)) {
                ldap.setAttribute(attributes[attribute], null);
            }
        }
    };

    /**
     * This method show a preview of a list of users or groups when the attributes changes.
     */
    ldap.showPreview = function ()
    {
        var type = $('#ldapObjectClass').data('type');
        var attributes = ldap.getAttributes(type);
        var idx = ldap.getEntryIndex();

        $('#ldapPreview, #ldapFooter').removeClass('hide');

        if (idx !== null) {
            for (var i = 0; i < 5; i++) {
                for (var name in attributes) {
                    if (attributes.hasOwnProperty(name) && ldap.entries.hasOwnProperty(idx + i) &&
                        ldap.entries[idx + i].hasOwnProperty(ldap[attributes[name]])
                    ) {
                        $('#ldapPreview #' + type + i + ' .' + attributes[name]).html(
                            ldap.entries[idx + i][ldap[attributes[name]]][0]
                        );
                    } else if (ldap[attributes[name]] === '') {
                        $('#ldapPreview #' + type + i + ' .' + attributes[name]).html('');
                    }
                }
            }
        }
    };

    /**
     * This method save the mapping settings of a LDAP server
     *
     * @param settings An array with the mapping settings (form serialized)
     */
    ldap.saveSettings = function (settings)
    {
        $.post(routing.generate('claro_admin_ldap_save_settings'), settings)
        .done(function (data) {
            if (data === 'true') {
                window.location.href = routing.generate('claro_admin_ldap_servers');
            } else {
                modal.error();
            }
        })
        .error(function () {
            modal.error();
        });
    };

    /**
    * Create or edit ldap server settings
    */
    ldap.updateServer = function (element, currentName)
    {
        var routeVars =  typeof(currentName) !== 'undefined' ? {'name': currentName} : null;

        modal.fromRoute('claro_admin_ldap_form', routeVars, function (modalElement) {
            modalElement.on('click', '.btn-primary', function (event) {
                event.preventDefault();

                var action = $('form', modalElement).attr('action');
                var form = $('form', modalElement).serializeArray();
                var name = form[0].value;

                if (action && form) {
                    $.post(action, form)
                    .done(function (data) {
                        if (data === 'true' && routeVars) {
                            modal.hide();
                            if (currentName !== name) {
                                $(element).text(name);
                            }
                        } else if (data === 'true') {
                            $('.ldap-settings .clearfix').append(
                                common.createElement('div', 'content-6 alert alert-default').append(
                                    common.createElement('button', 'close')
                                    .attr('type', 'button')
                                    .data('name', name)
                                    .html('&times;')
                                )
                                .append(
                                    common.createElement('a', 'pointer-hand alert-link').append(
                                        common.createElement('strong').html(name)
                                    )
                                )
                            );
                            modal.hide();
                        } else {
                            $(modalElement).html(data);
                        }
                    });
                }
            });
        });
    };

    /**
     * Delete server settings
     *
     * @param element The HTML object to remove after the delete
     * @param name The name of the LDAP server
     */
    ldap.deleteServer = function (element, name)
    {
        $.ajax(routing.generate('claro_admin_ldap_delete', {'name': name}))
        .done(function (data) {
            if (data === 'true') {
                $(element).hide('slow', function () {
                    $(element).remove();
                });
            } else {
                modal.error();
            }
        })
        .error(function () {
            modal.error();
        });
    };

    /**
     * Check if can save LDAP configuration
     */
    ldap.canSave = function (form, x, y)
    {
        var tmp = true;

        for (var i = x; i <= y; i++) {
            if (!form.hasOwnProperty(i) || form[i].value === '') {
                tmp = false;
            }
        }

        return tmp;
    };

    ldap.getEntryIndex = function()
    {
        if (ldap.entries !== null) {
            return (ldap.entries.hasOwnProperty(0) && ldap.entries[0].hasOwnProperty('count')) ? 0 :
              ((ldap.entries.hasOwnProperty(1) && ldap.entries[1].hasOwnProperty('count')) ? 1 : null);
        }

        return null;
    }


    /** events **/

    $('body').on('change', '#ldapObjectClass', function () {
        var element = this;
        var name = $(element).data('name');

        ldap.reset();

        if (element.value !== undefined && element.value !== '') {
            $.ajax(routing.generate('claro_admin_ldap_get_entries', {'objectClass': element.value, 'name': name}))
            .done(function (data) {
                ldap.setAttribute('entries', $.parseJSON(data));
                ldap.fillSelect();
            })
            .error(function () {
                modal.error();
            });
        }
    })
    .on('change', '#ldapAttributes select', function () {
        ldap.setAttribute($(this).attr('id'), this.value);
        ldap.showPreview();
    })
    .on('click', '#ldapFooter .btn-primary', function () {
        var form = $('#ldapForm').serializeArray();
        var type = $('#ldapObjectClass').data('type');

        if (ldap.canSave(form, 2, type === 'users' ? 5 : 3)) {
            ldap.saveSettings(form);
        } else {
            modal.simpleContainer(
                translator.trans(type + '_settings', {}, 'ldap'),
                translator.trans('ldap_save_' + type + '_settings_error', {}, 'ldap')
            );
        }
    })
    .on('click', '.ldap-settings .btn-primary', function () {
        ldap.updateServer(this);
    })
    .on('click', '.ldap-settings .alert.alert-default > .alert-link', function () {
        var name = $(this).text();

        if (name) {
            ldap.updateServer(this, name);
        }
    })
    .on('click', '.ldap-settings .alert.alert-default > .close', function () {
        var name = $(this).data('name');
        var element = $(this).parents('.alert').first();

        if (name) {
            modal.fromRoute('claro_content_confirm', null, function (modalElement) {
                modalElement.on('click', '.btn.delete', function () {
                    ldap.deleteServer(element, name);
                });
            });
        }
    })
    .on('change', '#userCreation', function () {
        $.ajax(
            routing.generate(
                'claro_admin_ldap_check_user_creation', {'state': $(this).prop('checked').toString()}
            )
        );
    })
    .on('click', '.ldap-export .list-group-item .badge', function () {
        var type = $(this).data('type');
        var name = $(this).parents('.list-group-item').first().data('name');

        modal.fromRoute('claro_admin_ldap_export_preview', {'type': type, 'name': name}, function (element) {
            element.on('click', '.modal-footer .btn-primary', function () {
                $(element).modal('hide');
                window.location.href = routing.generate(
                    'claro_admin_ldap_export_export_file', {'type': type.toLowerCase(), 'name': name}
                );
            });
        });
    });

}());
