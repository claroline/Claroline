import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
// the component to connect
import {ResourceMain as ResourceMainComponent} from '#/main/core/resource/components/main'
// the store to use
import {selectors} from '#/main/core/resource/store'

const ResourceMain = withRouter(connect(
  (state) => ({
    resourceType: selectors.resourceType(state)
  })
)(ResourceMainComponent))

export {
  ResourceMain
}
