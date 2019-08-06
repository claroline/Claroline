import {connect} from 'react-redux'

import {ConnectionModal as ConnectionModalComponent} from '#/main/app/modals/connection/components/modal'
import {actions} from '#/main/app/modals/connection/store'

const ConnectionModal = connect(
  null,
  (dispatch) => ({
    discard(messageId) {
      dispatch(actions.discard(messageId))
    }
  })
)(ConnectionModalComponent)

export {
  ConnectionModal
}
