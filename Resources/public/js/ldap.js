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
    var ldap = window.Claroline.LDAP;

    /**
     *
     */
    ldap.setAttribute = function (attribute, value)
    {
        ldap[attribute] = value;
    };

    /**
     *
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
     *
     */
    ldap.showPreview = function ()
    {
        var attributes = ['userName', 'firstName', 'lastName', 'email', 'password', 'code', 'locale'];

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

    $('body').on('change', '#ldapObjectClass', function () {
        var element = this;
        $('#ldapAttributes, #ldapPreview, #ldapFooter').addClass('hide');

        if (element.value !== undefined && element.value !== '') {
            $.ajax(routing.generate('claro_admin_ldap_get_users', {'objectClass': element.value}))
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
        //console.log(ldap.users);
        ldap.showPreview();
    });



}());
