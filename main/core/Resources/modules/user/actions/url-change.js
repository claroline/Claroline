import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_USER_PUBLIC_URL} from '#/main/core/user/modals'

export default (users, refresher, path, currentUser) => ({
  name: 'url-change',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-link',
  label: trans('change_profile_public_url'),
  scope: ['object'],
  modal: [MODAL_USER_PUBLIC_URL, {
    user: users[0],
    url: users[0].meta.publicUrl,
    onSave: (user) => refresher.update([user])
  }],
  displayed: hasPermission('edit', users[0]),
  disabled: users[0].meta.publicUrlTuned,
  group: trans('management')
})
