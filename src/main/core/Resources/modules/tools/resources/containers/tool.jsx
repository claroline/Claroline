import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {hasPermission} from '#/main/app/security'

import {selectors as toolSelectors} from '#/main/core/tool/store/selectors'
import {ResourcesTool as ResourcesToolComponent} from '#/main/core/tools/resources/components/tool'
import {reducer, selectors} from '#/main/core/tools/resources/store'


const ResourcesTool = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      root: selectors.root(state),
      canAdministrate: hasPermission('administrate', toolSelectors.toolData(state))
    })
  )(ResourcesToolComponent)
)

export {
  ResourcesTool
}
