import {connect} from 'react-redux'

// the store to use
import {actions, selectors} from '#/main/core/resource/store'

// the component to connect
import {ResourcePage} from '#/main/core/resource/components/page'

/**
 * Connected container for resources.
 *
 * Connects the <Resource> component to a redux store.
 * If you don't use redux in your implementation @see Resource functional component.
 */
const ResourcePageContainer = connect(
  (state) => ({
    embedded: selectors.embedded(state),
    resourceNode: selectors.resourceNode(state),
    userEvaluation: selectors.resourceEvaluation(state)
  }),
  (dispatch) => ({
    updateNode(resourceNode) {
      dispatch(actions.updateNode(resourceNode))
    }
  })
)(ResourcePage)

export {
  ResourcePageContainer
}
