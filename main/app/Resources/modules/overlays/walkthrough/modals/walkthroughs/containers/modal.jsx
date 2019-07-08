import {connect} from 'react-redux'

import {WalkthroughsModal as WalkthroughsModalComponent} from '#/main/app/overlays/walkthrough/modals/walkthroughs/components/modal'
import {actions} from '#/main/app/overlays/walkthrough/store'

const WalkthroughsModal = connect(
  null,
  (dispatch) => ({
    start(scenario, additional, documentation) {
      dispatch(actions.start(scenario, additional, documentation))
    }
  })
)(WalkthroughsModalComponent)

export {
  WalkthroughsModal
}
