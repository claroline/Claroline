
import {reducer} from '#/main/theme/administration/appearance/store'
import {AppearanceMenu} from '#/main/theme/administration/appearance/components/menu'
import {AppearanceTool} from '#/main/theme/administration/appearance/containers/tool'

export default {
  component: AppearanceTool,
  menu: AppearanceMenu,
  store: reducer,
  styles: ['claroline-distribution-main-theme-administration-appearance']
}
