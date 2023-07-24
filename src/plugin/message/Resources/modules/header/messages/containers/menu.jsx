import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {selectors as configSelectors} from '#/main/app/config/store'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {MessagesMenu as MessagesMenuComponent} from '#/plugin/message/header/messages/components/menu'
import {actions, reducer, selectors} from '#/plugin/message/header/messages/store'

const MessagesMenu = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      isAuthenticated: securitySelectors.isAuthenticated(state),
      refreshDelay: configSelectors.param(state, 'notifications.refreshDelay'),
      count: selectors.count(state)
    }),
    (dispatch) => ({
      countMessages() {
        return dispatch(actions.countMessages())
      }
    })
  )(MessagesMenuComponent)
)

export {
  MessagesMenu
}
