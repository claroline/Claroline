import {trans} from '#/main/app/intl/translation'
import {URL_BUTTON} from '#/main/app/buttons'

export default (rows) => ({
  type: URL_BUTTON,
  icon: 'fa fa-fw fa-mask',
  label: trans('show_as'),
  target: ['claro_desktop_open', {_switch: rows[0].username}],
  scope: ['object']
})
