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
        'users': null,
        'userName': null,
        'firstName': null,
        'lastName': null,
        'email': null,
        'password': null,
        'code': null,
        'locale': null
    };

    var routing = window.Routing;
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
    ldap.getAttributes = function () {
        return ['userName', 'firstName', 'lastName', 'email', 'password', 'code', 'locale'];
    };

    /**
     * This method fill select with a list of LDAP subObjectClass (after choose the father LDAP objecClass).
     */
    ldap.fillSelect = function ()
    {
        if (ldap.users.hasOwnProperty(1) && ldap.users[1].hasOwnProperty('count')) {
            var select = $(document.createElement('select'));
            select.append($(document.createElement('option')));

            for (var i = 0; i < ldap.users[1].count; i++) {
                select.append($(document.createElement('option')).html(ldap.users[1][i]));
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
        var attributes = ldap.getAttributes();
        for (var attribute in attributes) {
            if (attributes.hasOwnProperty(attribute)) {
                console.log(attributes[attribute], ldap[attributes[attribute]]);
                ldap.setAttribute(attributes[attribute], null);
            }
        }
    };

    /**
     * This method show a preview of a list of users when the attributes changes.
     */
    ldap.showPreview = function ()
    {
        var attributes = ldap.getAttributes();

        $('#ldapPreview, #ldapFooter').removeClass('hide');

        if (ldap.users.hasOwnProperty(1) && ldap.users[1].hasOwnProperty('count')) {
            for (var i = 1; i < 6; i++) {
                for (var name in attributes) {
                    if (attributes.hasOwnProperty(name) &&
                        ldap.users.hasOwnProperty(i) &&
                        ldap.users[i].hasOwnProperty(ldap[attributes[name]])
                    ) {
                        $('#ldapPreview #user' + i + ' .' + attributes[name]).html(
                            ldap.users[i][ldap[attributes[name]]][0]
                        );
                    } else if (ldap[attributes[name]] === '') {
                        $('#ldapPreview #user' + i + ' .' + attributes[name]).html('');
                    }
                }
            }
        }
    };

    /**
    * Create or edit ldap server settings
    */
    ldap.addServer = function (element, currentHost)
    {
        var routeVars =  typeof(currentHost) !== 'undefined' ? {'host': currentHost} : null;

        modal.fromRoute('claro_admin_ldap_form', routeVars, function (modalElement) {
            modalElement.on('click', '.btn-primary', function (event) {
                event.preventDefault();

                var action = $('form', modalElement).attr('action');
                var form = $('form', modalElement).serializeArray();
                var host = form[0].value;

                if (action && form) {
                    $.post(action, form)
                    .done(function (data) {
                        if (data === 'true' && routeVars) {
                            modal.hide();
                            if (currentHost !== host) {
                                $(element).text(host);
                            }
                        } else if (data === 'true') {
                            $('.ldap-settings .clearfix').append(
                                common.createElement('div', 'content-6 alert alert-default').append(
                                    common.createElement('button', 'close')
                                    .attr('type', 'button')
                                    .data('host', host)
                                    .html('&times;')
                                )
                                .append(
                                    common.createElement('a', 'pointer-hand alert-link').append(
                                        common.createElement('strong').html(host)
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
     * @param host The host name of the LDAP server
     */
    ldap.deleteServer = function (element, host)
    {
        $.ajax(routing.generate('claro_admin_ldap_delete', {'host': host}))
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

    $('body').on('change', '#ldapObjectClass', function () {
        var element = this;
        var host = $(element).data('host');

        ldap.reset();

        if (element.value !== undefined && element.value !== '') {
            $.ajax(routing.generate('claro_admin_ldap_get_users', {'objectClass': element.value, 'host': host}))
            .done(function (data) {
                ldap.setAttribute('users', $.parseJSON(data));
                ldap.fillSelect();
            })
            .error(function () {
                modal.error();
            });
        }
    }).on('change', '#ldapAttributes select', function () {
        ldap.setAttribute($(this).attr('id'), this.value);
        ldap.showPreview();
    }).on('click', '#ldapFooter .btn-primary', function () {

        var options = ldap.getAttributes();
        var values = [];

        for (var option in options) {
            if (options.hasOwnProperty(option)) {
                values[options[option]] = $('#ldapAttributes #' + options[option]).val();
                if (values[options[option]]) {
                    console.log(values[options[option]]);
                }
            }
        }

        console.log(values);
    })
    .on('click', '.ldap-settings .btn-primary', function () {
        ldap.addServer(this);
    }).on('click', '.ldap-settings .alert.alert-default > .alert-link', function () {
        var host = $(this).text();

        if (host) {
            ldap.addServer(this, host);
        }
    })
    .on('click', '.ldap-settings .alert.alert-default > .close', function () {
        var host = $(this).data('host');
        var element = $(this).parents('.alert').first();

        if (host) {
            modal.fromRoute('claro_content_confirm', null, function (modalElement) {
                modalElement.on('click', '.btn.delete', function () {
                    ldap.deleteServer(element, host);
                });
            });
        }
    }).on('change', '#userCreation', function () {
        $.ajax(
            routing.generate(
                'claro_admin_ldap_check_user_creation', {'state': $(this).prop('checked').toString()}
            )
        );
    });

}());
