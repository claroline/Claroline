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
      basePath: selectors.basePath(state),
      contextType: toolSelectors.contextType(state),
      currentUser: securitySelectors.currentUser(state),
      loaded: selectors.loaded(state),
      embedded: selectors.embedded(state),
      showHeader: selectors.showHeader(state),
      managed: selectors.managed(state),
      resourceNode: selectors.resourceNode(state),
      userEvaluation: selectors.resourceEvaluation(state),
      accessErrors: selectors.accessErrors(state),
      serverErrors: selectors.serverErrors(state)
    }),
    (dispatch) => ({
      updateNode(resourceNode) {
        dispatch(actions.updateNode(resourceNode))
      },
      loadResource(resourceNode, embedded = false) {
        dispatch(actions.fetchResource(resourceNode, embedded))
      },
      dismissRestrictions() {
        dispatch(actions.dismissRestrictions(true))
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
