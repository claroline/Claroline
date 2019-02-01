import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

export default (rows, refresher) => ({
  type: CALLBACK_BUTTON,
  icon: 'fa fa-fw fa-book',
  label: trans('enable_personal_ws'),
  scope: ['object', 'collection'],
  displayed: 0 < rows.filter(u => !u.meta.personalWorkspace).length,
  callback: () => refresher.createWorkspace(rows)
}
)
