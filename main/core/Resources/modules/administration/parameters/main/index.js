
import {reducer} from '#/main/core/administration/parameters/main/store/reducer'
import {ParametersTool} from '#/main/core/administration/parameters/main/containers/tool'
import {ParametersMenu} from '#/main/core/administration/parameters/main/components/menu'

export default {
  component: ParametersTool,
  menu: ParametersMenu,
  store: reducer,
  styles: ['claroline-distribution-main-core-administration-parameters']
}
