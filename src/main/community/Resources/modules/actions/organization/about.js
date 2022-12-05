import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_ORGANIZATION_ABOUT} from '#/main/community/organization/modals/about'

export default (organizations) => ({
  name: 'about',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-circle-info',
  label: trans('show-info', {}, 'actions'),
  displayed: hasPermission('open', organizations[0]),
  modal: [MODAL_ORGANIZATION_ABOUT, {
    organizationId: organizations[0].id
  }],
  scope: ['object']
})
