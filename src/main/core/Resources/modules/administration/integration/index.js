import {reducer} from '#/main/core/administration/integration/store/reducer'
import {IntegrationTool} from '#/main/core/administration/integration/containers/tool'
import {IntegrationMenu} from '#/main/core/administration/integration/components/menu'

export default {
  component: IntegrationTool,
  menu: IntegrationMenu,
  store: reducer,
  styles: ['claroline-distribution-main-core-administration-integration']
}
