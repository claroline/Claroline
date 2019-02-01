import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

export default (rows, refresher) => ({
  type: CALLBACK_BUTTON,
  icon: 'fa fa-fw fa-user-lock',
  label: trans('reset_password'),
  scope: ['object', 'collection'],
  callback: () => refresher.resetPassword(rows)
})
