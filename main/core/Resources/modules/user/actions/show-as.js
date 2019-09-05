import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {URL_BUTTON} from '#/main/app/buttons'

export default (rows) => ({
  type: URL_BUTTON,
  icon: 'fa fa-fw fa-mask',
  label: trans('show_as'),
  scope: ['object'],
  target: url(['claro_index', {_switch: rows[0].username}])+'#/desktop'
})
