import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {HomeTool as HomeToolComponent} from '#/plugin/home/tools/home/components/tool'

const HomeTool = connect(
  (state) => ({
    canEdit: hasPermission('edit', toolSelectors.toolData(state))
  })
)(HomeToolComponent)

export {
  HomeTool
}
