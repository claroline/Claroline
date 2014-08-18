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
    
    var currentTokenId;
    
    $('.delete-security-token-btn').on('click', function () {
        currentTokenId = $(this).data('token-id');
        $('#delete-security-token-validation-box').modal('show');
    });
    
    $('#delete-security-token-confirm-ok').on('click', function () {
        $('#delete-security-token-validation-box').modal('hide');
        
        window.location = Routing.generate(
            'claro_admin_security_token_delete',
            {'tokenId': currentTokenId}
        );
    });
})();