import {connect} from 'react-redux'

// the component to connect
import {ResourceMain as ResourceMainComponent} from '#/main/core/resource/components/main'
// the store to use
import {selectors} from '#/main/core/resource/store'

const ResourceMain = connect(
  (state) => ({
    resourceType: selectors.resourceType(state)
  })
)(ResourceMainComponent)

export {
  ResourceMain
}
