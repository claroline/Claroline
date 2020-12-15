import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {AppearanceTool as AppearanceToolComponent} from '#/main/theme/administration/appearance/components/tool'

const AppearanceTool = connect(
  (state) => ({
    path: toolSelectors.path(state)
  })
)(AppearanceToolComponent)

export {
  AppearanceTool
}
