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
    
    var charsString = '0123456789abcdefghiklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXTZ';
    var charsList = charsString.split('');
    
    function generateRandomString(length, withTime)
    {
        var result = '';
        var tokenLength = (typeof length === 'number') ?
            parseInt(length) :
            8;
    
        for (var i = 0; i < tokenLength; i++) {
            result += charsList[Math.floor(Math.random() * charsList.length)];
        }
        
        if (withTime) {
            var currentTime = new Date().getTime();
            result += currentTime;
        }
        
        return result;
    }
    
    $('#generate-token-btn').on('click', function () {
        var randomString = generateRandomString(12, true);
        
        $('#security_token_form_token').val(randomString);
    });
})();