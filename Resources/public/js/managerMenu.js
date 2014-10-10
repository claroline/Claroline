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
    var currentTeamId;
    
    $('.delete-team-btn').on('click', function () {
        currentTeamId = $(this).data('team-id');
        window.Claroline.Modal.confirmRequest(
            Routing.generate(
                'claro_team_delete',
                {'team': currentTeamId}
            ),
            removeTeamRow,
            null,
            Translator.get('team:delete_team_comfirm_message'),
            Translator.get('team:delete_team')
        );
    });
    
    var removeTeamRow = function () {
        $('#row-team-' + currentTeamId).remove();
    }
    
    var refreshPage = function () {
        window.location.reload();
    }
})();