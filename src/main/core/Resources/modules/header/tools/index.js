import {trans} from '#/main/app/intl/translation'
import {ToolsMenu} from '#/main/core/header/tools/containers/menu'

// expose main component to be used by the header
export default ({
  name: 'tools',
  label: trans('tools'),
  component: ToolsMenu
})
