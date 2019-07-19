import {trans} from '#/main/app/intl/translation'
import {URL_BUTTON} from '#/main/app/buttons'

export default (rows) => ({
  type: URL_BUTTON,
  icon: 'fa fa-fw fa-address-card',
  label: trans('show_profile'),
  target: ['claro_user_profile', {user: rows[0].meta.publicUrl}],
  scope: ['object']
})
