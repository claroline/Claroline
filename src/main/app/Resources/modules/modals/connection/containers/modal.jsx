import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store/selectors'

import {ConnectionModal as ConnectionModalComponent} from '#/main/app/modals/connection/components/modal'
import {actions} from '#/main/app/modals/connection/store'

const ConnectionModal = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state)
  }),
  (dispatch) => ({
    discard(messageId) {
      dispatch(actions.discard(messageId))
    }
  })
)(ConnectionModalComponent)

export {
  ConnectionModal
}
