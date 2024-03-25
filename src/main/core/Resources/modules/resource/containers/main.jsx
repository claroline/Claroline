import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {withReducer} from '#/main/app/store/components/withReducer'

// the component to connect
import {ResourceMain as ResourceMainComponent} from '#/main/core/resource/components/main'
// the store to use
import {actions, reducer, selectors} from '#/main/core/resource/store'

const ResourceMain = /*withRouter(*/
  /*withReducer(selectors.STORE_NAME, reducer)(*/
    connect(
      (state) => ({
        /*path: selectors.path(state),*/
        //resourceSlug: selectors.slug(state),
        //resourceType: selectors.resourceType(state),
        //embedded: selectors.embedded(state),
        //loaded: selectors.loaded(state),
        //notFound: selectors.notFound(state)
      }),
      (dispatch) => ({
        /*open(resourceSlug, embedded) {
          console.log(embedded)
          return dispatch(actions.fetchResource(resourceSlug, embedded))
        },
        load(type, data) {
          console.log(type)
          console.log(data)
          // load resource data inside the store
          dispatch(actions.loadResourceType(type, data))

          // mark the resource as loaded
          // it's done through another action (not RESOURCE_LOAD) to be sure all reducers have been resolved
          // and store is up-to-date
          dispatch(actions.setResourceLoaded(true))
        }*/
      })
    )(ResourceMainComponent)
  /*)*/
/*)*/

export {
  ResourceMain
}
