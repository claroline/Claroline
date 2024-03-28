import {withReducer} from '#/main/app/store/reducer'

import {IntegrationTool as IntegrationToolComponent} from '#/main/core/administration/integration/components/tool'
import {reducer, selectors} from '#/main/core/administration/integration/store'

const IntegrationTool = withReducer(selectors.store_NAME, reducer)(IntegrationToolComponent)

export {
  IntegrationTool
}
