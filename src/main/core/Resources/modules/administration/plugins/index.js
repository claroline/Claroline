import {reducer} from '#/main/core/administration/plugins/store/reducer'
import {PluginsTool} from '#/main/core/administration/plugins/containers/tool'
import {PluginsMenu} from '#/main/core/administration/plugins/components/menu'

export default {
  component: PluginsTool,
  menu: PluginsMenu,
  store: reducer,
  styles: ['claroline-distribution-main-core-administration-plugins']
}
