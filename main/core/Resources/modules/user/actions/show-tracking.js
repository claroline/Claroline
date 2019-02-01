import {trans} from '#/main/app/intl/translation'
import {URL_BUTTON} from '#/main/app/buttons'

export default (rows) => ({
  type: URL_BUTTON,
  icon: 'fa fa-fw fa-line-chart',
  label: trans('show_tracking'),
  target: ['claro_user_tracking', {publicUrl: rows[0].meta.publicUrl}],
  scope: ['object']
})
