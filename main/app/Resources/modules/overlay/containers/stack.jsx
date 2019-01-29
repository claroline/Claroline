import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

// the store to use
import {reducer, selectors} from '#/main/app/overlay/store'
// the component to connect
import {OverlayStack as OverlayStackComponent} from '#/main/app/overlay/components/stack'

const OverlayStack = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      show: selectors.show(state)
    }),
    undefined,
    undefined,
    {
      areStatesEqual: (next, prev) => selectors.store(prev) === selectors.store(next)
    }
  )(OverlayStackComponent)
)

export {
  OverlayStack
}
