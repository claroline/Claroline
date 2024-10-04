import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

// the component to connect
import {ResourceWrapper as ResourceWrapperComponent} from '#/main/core/resource/components/wrapper'
// the store to use
import {actions, reducer, selectors} from '#/main/core/resource/store'

const ResourceWrapper = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      path: selectors.path(state),
      loaded: selectors.loaded(state),
      notFound: selectors.notFound(state)
    }),
    (dispatch) => ({
      fetch(resourceSlug, embedded) {
        return dispatch(actions.fetchResource(resourceSlug, embedded))
      },
      open(resourceSlug, embedded) {
        return dispatch(actions.open(resourceSlug, embedded))
      },
      openType(resourceType, resourceSlug, resourceData) {
        return dispatch(actions.loadResourceType(resourceType, resourceData))
      }
    })
  )(ResourceWrapperComponent)
)

export {
  ResourceWrapper
}
