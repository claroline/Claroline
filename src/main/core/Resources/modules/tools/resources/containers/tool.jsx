import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {hasPermission} from '#/main/app/security'

import {selectors as toolSelectors} from '#/main/core/tool/store/selectors'
import {actions as resourceActions, reducer as resourceReducer, selectors as resourceSelectors} from '#/main/core/resource/store'
import {ResourcesTool as ResourcesToolComponent} from '#/main/core/tools/resources/components/tool'
import {selectors} from '#/main/core/tools/resources/store'


const ResourcesTool = withReducer(resourceSelectors.STORE_NAME, resourceReducer)(
  connect(
    (state) => ({
      root: selectors.root(state),
      canAdministrate: hasPermission('administrate', toolSelectors.toolData(state))
    }),
    (dispatch) => ({
      openResource(resourceSlug) {
        dispatch(resourceActions.openResource(resourceSlug))
      }
    })
  )(ResourcesToolComponent)
)

export {
  ResourcesTool
}
