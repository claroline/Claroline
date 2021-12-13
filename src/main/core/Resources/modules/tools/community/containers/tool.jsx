import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {hasPermission} from '#/main/app/security'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors as profileSelectors} from '#/main/core/user/profile/store/selectors'
import {actions as userActions} from '#/main/core/tools/community/user/store'

import {CommunityTool as CommunityToolComponent} from '#/main/core/tools/community/components/tool'

const CommunityTool = withRouter(
  connect(
    (state) => ({
      contextType: toolSelectors.contextType(state),
      contextData: toolSelectors.contextData(state),
      currentUser: securitySelectors.currentUser(state),
      workspace: toolSelectors.contextData(state),
      canAdministrate: hasPermission('administrate', toolSelectors.toolData(state))
    }),
    (dispatch) => ({
      loadUser(username) {
        dispatch(userActions.open(profileSelectors.FORM_NAME, username))
      }
    })
  )(CommunityToolComponent)
)

export {
  CommunityTool
}
