import {trans} from '#/main/app/intl/translation'
import {AuthenticationMenu} from '#/main/authentication/header/authentication/containers/menu'

// expose main component to be used by the header
export default ({
  name: 'authentication',
  label: trans('authentication'),
  component: AuthenticationMenu
})
