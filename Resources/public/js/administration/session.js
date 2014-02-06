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

    var formDisplay = {
        'native': {
            'session_db_table': false,
            'session_db_id_col': false,
            'session_db_data_col': false,
            'session_db_dsn': false,
            'session_db_user': false,
            'session_db_password': false,
            'session_db_time_col': false,
            'cookie_lifetime': true
        },
        'claro_pdo': {
            'session_db_table': false,
            'session_db_id_col': false,
            'session_db_data_col': false,
            'session_db_dsn': false,
            'session_db_user': false,
            'session_db_password': false,
            'session_db_time_col': false,
            'cookie_lifetime': true
        },
        'pdo': {
            'session_db_table': true,
            'session_db_id_col': true,
            'session_db_data_col': true,
            'session_db_dsn': true,
            'session_db_user': true,
            'session_db_password': true,
            'session_db_time_col': true,
            'cookie_lifetime': true
        }
    };

    function display() {
        var storage = $('#platform_session_form_session_storage_type option:selected').val();
        var properties = formDisplay[storage];
        for (var item in properties) {
            var formElement = $('#platform_session_form_' + item)[0].parentElement.parentElement;
            properties[item] ? $(formElement).show():  $(formElement).hide();
        }
    }

    display();

    $('#platform_session_form_session_storage_type').change(function() {
        display();
    });
})();