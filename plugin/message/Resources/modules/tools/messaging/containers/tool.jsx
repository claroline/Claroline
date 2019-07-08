import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {MessagingTool as MessagingToolComponent} from '#/plugin/message/tools/messaging/components/tool'
import {actions} from '#/plugin/message/tools/messaging/store'

const MessagingTool = withRouter(
  connect(
    null,
    (dispatch) => ({
      openMessage(id) {
        dispatch(actions.openMessage(id))
      },
      newMessage(id) {
        dispatch(actions.newMessage(id))
      },
      addContacts(users) {
        dispatch(actions.addContacts(users))
      },
      setAsReply() {
        dispatch(actions.setAsReply())
      }
    })
  )(MessagingToolComponent)
)

export {
  MessagingTool
}
