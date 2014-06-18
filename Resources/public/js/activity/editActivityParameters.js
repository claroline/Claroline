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

    function hideRuleAction()
    {
        var primaryResourceType = $('#activity_form_primaryResource').data('type');
        $('.rule-action-option').addClass('hidden');
        $('.option-type-' + primaryResourceType).removeClass('hidden');
    }
    
    $('#activity_form_primaryResource').on('change', function () {
        hideRuleAction();
    });
    
    hideRuleAction();
})();