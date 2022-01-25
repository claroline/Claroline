import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

// the component to connect
import {ResourcePage as ResourcePageComponent} from '#/main/core/resource/components/page'
// the store to use
import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {actions, selectors} from '#/main/core/resource/store'

/**
 * Connected container for resources.
 *
 * Connects the <Resource> component to a redux store.
 * If you don't use redux in your implementation @see Resource functional component.
 */
const ResourcePage = withRouter(
  connect(
    (state) => ({
      currentUser: securitySelectors.currentUser(state),
      authenticated: securitySelectors.isAuthenticated(state),
      // tool params
      basePath: selectors.basePath(state),
      contextType: toolSelectors.contextType(state),
      // resource params
      embedded: selectors.embedded(state),
      showHeader: selectors.showHeader(state),
      managed: selectors.managed(state),
      resourceNode: selectors.resourceNode(state),
      userEvaluation: selectors.resourceEvaluation(state),
      accessErrors: selectors.accessErrors(state)
    }),
    (dispatch) => ({
      reload() {
        dispatch(actions.setResourceLoaded(false))
      },
      dismissRestrictions() {
        dispatch(actions.dismissRestrictions())
      },
      checkAccessCode(resourceNode, code, embedded = false) {
        dispatch(actions.checkAccessCode(resourceNode, code, embedded))
      }
    })
  )(ResourcePageComponent)
)

export {
  ResourcePage
}
