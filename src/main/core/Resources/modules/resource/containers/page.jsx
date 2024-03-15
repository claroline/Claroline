import {connect} from 'react-redux'

// the component to connect
import {ResourcePage as ResourcePageComponent} from '#/main/core/resource/components/page'
// the store to use
import {selectors} from '#/main/core/resource/store'

/**
 * Connected container for resources.
 *
 * Connects the <Resource> component to a redux store.
 * If you don't use redux in your implementation @see Resource functional component.
 */
const ResourcePage = connect(
  (state) => ({
    // tool params
    basePath: selectors.basePath(state),
    // resource params
    type: selectors.resourceType(state),
    embedded: selectors.embedded(state),
    showHeader: selectors.showHeader(state),
    resourceNode: selectors.resourceNode(state),
    userEvaluation: selectors.resourceEvaluation(state),
    accessErrors: selectors.accessErrors(state)
  })
)(ResourcePageComponent)

export {
  ResourcePage
}
