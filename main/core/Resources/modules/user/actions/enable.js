import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

export default (rows, refresher) => ({
  type: CALLBACK_BUTTON,
  icon: 'fa fa-fw fa-check-circle',
  label: trans('enable_user'),
  scope: ['object', 'collection'],
  displayed: 0 < rows.filter(u => u.restrictions.disabled).length,
  callback: () => refresher.enable(rows)
})
