import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {actions, reducer, selectors} from '#/plugin/message/modals/message/store'
import {MessageModal as MessageModalComponent} from '#/plugin/message/modals/message/components/message'

const MessageModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    null,
    (dispatch) => ({
      send(receivers, message) {
        dispatch(actions.sendMessage(receivers, message.object, message.content))
      }
    })
  )(MessageModalComponent)
)

export {
  MessageModal
}