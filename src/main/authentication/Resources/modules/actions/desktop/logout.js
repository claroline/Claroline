import {trans} from '#/main/app/intl'
import {URL_BUTTON} from '#/main/app/buttons'

export default () => ({
  name: 'logout',
  type: URL_BUTTON,
  icon: 'fa fa-fw fa-power-off',
  label: trans('logout'),
  target: ['claro_security_logout']
})
