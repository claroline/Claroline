import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'

import {selectors} from '#/plugin/message/tools/messaging/store/selectors'
import {MessagingParameters as MessagingParametersComponent} from '#/plugin/message/tools/messaging/components/parameters'

const MessagingParameters = connect(
  (state) => ({
    mailNotified: selectors.mailNotified(state),
    currentUser: securitySelectors.currentUser(state)
  })
)(MessagingParametersComponent)

export {
  MessagingParameters
}
