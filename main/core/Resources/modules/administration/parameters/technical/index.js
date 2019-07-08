
import {reducer} from '#/main/core/administration/parameters/technical/store/reducer'
import {TechnicalTool} from '#/main/core/administration/parameters/technical/components/tool'
import {TechnicalMenu} from '#/main/core/administration/parameters/technical/components/menu'

export default {
  component: TechnicalTool,
  menu: TechnicalMenu,
  store: reducer
}
