import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_TEAM_ABOUT} from '#/main/community/team/modals/about'

export default (teams) => ({
  name: 'about',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-circle-info',
  label: trans('show-info', {}, 'actions'),
  displayed: hasPermission('open', teams[0]),
  modal: [MODAL_TEAM_ABOUT, {
    teamId: teams[0].id
  }],
  scope: ['object']
})
