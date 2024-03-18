import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {AppearanceTool as AppearanceToolComponent} from '#/main/theme/administration/appearance/components/tool'
import {reducer, selectors} from '#/main/theme/administration/appearance/store'

const AppearanceTool = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      path: toolSelectors.path(state)
    })
  )(AppearanceToolComponent)
)

export {
  AppearanceTool
}
