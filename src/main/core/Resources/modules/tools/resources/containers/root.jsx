import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'
import {actions as listActions} from '#/main/app/content/list/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {ResourcesRoot as ResourcesRootComponent} from '#/main/core/tools/resources/components/root'
import {selectors} from '#/main/core/tools/resources/store'

const ResourcesRoot = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state),
    path: toolSelectors.path(state),
    listName: selectors.LIST_ROOT_NAME
  }),
  (dispatch) => ({
    invalidate() {
      dispatch(listActions.invalidateData(selectors.LIST_ROOT_NAME))
    }
  })
)(ResourcesRootComponent)

export {
  ResourcesRoot
}
