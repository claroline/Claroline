import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {withReducer} from '#/main/app/store/components/withReducer'

import {actions as resourceActions, reducer as resourceReducer, selectors as resourceSelectors} from '#/main/core/resource/store'
import {ResourcesTool as ResourcesToolComponent} from '#/main/core/tools/resources/components/tool'
import {selectors} from '#/main/core/tools/resources/store'

const ResourcesTool = withRouter(
  withReducer(resourceSelectors.STORE_NAME, resourceReducer)(
    connect(
      (state) => ({
        root: selectors.root(state)
      }),
      (dispatch) => ({
        openResource(resourceSlug) {
          dispatch(resourceActions.openResource(resourceSlug))
        }
      })
    )(ResourcesToolComponent)
  )
)

export {
  ResourcesTool
}
