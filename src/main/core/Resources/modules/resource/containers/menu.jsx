import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {ResourceMenu as ResourceMenuComponent} from '#/main/core/resource/components/menu'
import {selectors, actions} from '#/main/core/resource/store'

const ResourceMenu = withRouter(
  connect(
    (state) => ({
      path: selectors.path(state),
      basePath: selectors.basePath(state),
      resourceNode: selectors.resourceNode(state),
      currentUser: securitySelectors.currentUser(state)
    }),
    (dispatch) => ({
      reload() {
        dispatch(actions.setResourceLoaded(false))
      }
    })
  )(ResourceMenuComponent)
)

export {
  ResourceMenu
}
