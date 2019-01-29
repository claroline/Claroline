import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {WalkthroughOverlay as WalkthroughOverlayComponent} from '#/main/app/overlay/walkthrough/components/overlay'
import {actions, selectors, reducer} from '#/main/app/overlay/walkthrough/store'

const WalkthroughOverlay = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      // current progression
      show: selectors.show(state),
      progression: selectors.progression(state),
      current: selectors.currentStep(state),
      hasNext: selectors.hasNext(state),
      hasPrevious: selectors.hasPrevious(state),

      // general info
      additional: selectors.additional(state)
    }),
    (dispatch) => ({
      start(steps, additional, documentation) {
        dispatch(actions.start(steps, additional, documentation))
      },
      restart() {
        dispatch(actions.restart())
      },
      skip() {
        dispatch(actions.skip())
      },
      next() {
        dispatch(actions.next())
      },
      previous() {
        dispatch(actions.previous())
      },
      finish() {
        dispatch(actions.finish())
      }
    }),
    undefined,
    {
      areStatesEqual: (next, prev) => selectors.store(prev) === selectors.store(next)
    }
  )(WalkthroughOverlayComponent)
)

export {
  WalkthroughOverlay
}
