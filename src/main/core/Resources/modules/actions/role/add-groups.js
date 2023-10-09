import {trans} from '#/main/app/intl/translation'
import {url} from '#/main/app/api'

import {ASYNC_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {MODAL_GROUPS} from '#/main/community/modals/groups'

export default (role, refresher) => ({
  name: 'add-groups',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-users',
  label: trans('add_group'),
  modal: [MODAL_GROUPS, {
    selectAction: (groups) => ({
      type: ASYNC_BUTTON,
      request: {
        url: url(['apiv2_role_add_groups', {id: role[0].id}], {ids: groups}),
        request: {
          method: 'PATCH'
        },
        success: () => refresher.update(role)
      }
    })
  }],
  group: trans('registration'),
  scope: ['object', 'collection']
})
