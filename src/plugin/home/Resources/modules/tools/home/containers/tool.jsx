import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/reducer'
import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {HomeTool as HomeToolComponent} from '#/plugin/home/tools/home/components/tool'
import {reducer, selectors} from '#/plugin/home/tools/home/store'

const HomeTool = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      canEdit: hasPermission('edit', toolSelectors.toolData(state))
    })
  )(HomeToolComponent)
)

export {
  HomeTool
}
