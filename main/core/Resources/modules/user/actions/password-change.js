import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

export default (rows, refresher) => ({
  type: CALLBACK_BUTTON,
  icon: 'fa fa-fw fa-lock',
  label: trans('change_password'),
  scope: ['object'],
  callback: () => refresher.updatePassword(rows[0]),
  dangerous: true
})
