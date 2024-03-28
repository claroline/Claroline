import {connect} from 'react-redux'
import {withReducer} from '#/main/app/store/reducer'

import {MessagingTool as MessagingToolComponent} from '#/plugin/message/tools/messaging/components/tool'
import {actions, reducer, selectors} from '#/plugin/message/tools/messaging/store'

const MessagingTool = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    null,
    (dispatch) => ({
      openMessage(id) {
        dispatch(actions.openMessage(id))
      },
      addContacts(users) {
        dispatch(actions.addContacts(users))
      }
    })
  )(MessagingToolComponent)
)

export {
  MessagingTool
}
