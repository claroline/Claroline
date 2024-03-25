import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {withReducer} from '#/main/app/store/components/withReducer'

// the component to connect
import {ResourceWrapper as ResourceWrapperComponent} from '#/main/core/resource/components/wrapper'
// the store to use
import {actions, reducer, selectors} from '#/main/core/resource/store'

const ResourceWrapper = /*withRouter(*/
  withReducer(selectors.STORE_NAME, reducer)(
    connect(
      (state) => ({
        path: selectors.path(state),
        //resourceSlug: selectors.slug(state),
        //resourceType: selectors.resourceType(state),
        //embedded: selectors.embedded(state),
        loaded: selectors.loaded(state),
        notFound: selectors.notFound(state)
      }),
      (dispatch) => ({
        open(resourceSlug, embedded) {
          return dispatch(actions.fetchResource(resourceSlug, embedded))
        },
        openType(resourceType, resourceSlug, resourceData) {
          return dispatch(actions.loadResourceType(resourceType, resourceData))
        }
      })
    )(ResourceWrapperComponent)
  )
/*)*/

export {
  ResourceWrapper
}
