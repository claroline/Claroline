import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {withReducer} from '#/main/app/store/components/withReducer'

import {ResourceMenu as ResourceMenuComponent} from '#/main/core/resource/components/menu'
import {selectors, reducer} from '#/main/core/resource/store'

const ResourceMenu = withRouter(
  withReducer(selectors.STORE_NAME, reducer)(
    connect(
      (state) => ({
        path: selectors.path(state),
        loaded: selectors.loaded(state),
        resourceId: selectors.id(state),
        resourceType: selectors.resourceType(state)
      })
    )(ResourceMenuComponent)
  )
)

export {
  ResourceMenu
}
