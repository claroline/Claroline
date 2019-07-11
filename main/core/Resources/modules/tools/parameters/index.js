import {reducer} from '#/main/core/tools/parameters/store/reducer'
import {ParametersTool} from '#/main/core/tools/parameters/components/tool'
import {Menu} from '#/main/core/tools/parameters/components/menu'

export default {
  menu: Menu,
  component: ParametersTool,
  store: reducer
}
