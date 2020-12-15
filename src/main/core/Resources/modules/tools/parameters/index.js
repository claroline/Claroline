import {reducer} from '#/main/core/tools/parameters/store/reducer'
import {ParametersTool} from '#/main/core/tools/parameters/components/tool'
import {ParametersMenu} from '#/main/core/tools/parameters/components/menu'

export default {
  menu: ParametersMenu,
  component: ParametersTool,
  store: reducer
}
