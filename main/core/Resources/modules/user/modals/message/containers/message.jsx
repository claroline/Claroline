import {connect} from 'react-redux'

import {actions} from '#/main/core/user/message/actions'
import {MessageModal as MessageModalComponent} from '#/main/core/user/modals/message/components/message'

const MessageModal = connect(
  null,
  (dispatch) => ({
    send(destinators, message) {
      dispatch(actions.sendMessage(destinators, message.object, message.content))
    }
  })
)(MessageModalComponent)

export {
  MessageModal
}