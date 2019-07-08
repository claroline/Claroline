
import {reducer} from '#/main/core/administration/parameters/appearance/store/reducer'
import {AppearanceTool} from '#/main/core/administration/parameters/appearance/components/tool'
import {AppearanceMenu} from '#/main/core/administration/parameters/appearance/components/menu'

export default {
  component: AppearanceTool,
  menu: AppearanceMenu,
  store: reducer
}
