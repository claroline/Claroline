import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {actions as listActions} from '#/main/app/content/list/store'

import {ResourcesTool as ResourcesToolComponent} from '#/main/core/tools/resources/components/tool'
import {selectors} from '#/main/core/tools/resources/store'

const ResourcesTool = withRouter(
  connect(
    (state) => ({
      currentUser: securitySelectors.currentUser(state),
      root: selectors.root(state),
      listRootName: selectors.LIST_ROOT_NAME
    }),
    (dispatch) => ({
      invalidateRoot() {
        dispatch(listActions.invalidateData(selectors.LIST_ROOT_NAME))
      }
    })
  )(ResourcesToolComponent)
)

export {
  ResourcesTool
}
