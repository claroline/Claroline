import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {UserTool as UserToolComponent}  from '#/main/core/workspace/user/components/tool'
import {select}  from '#/main/core/workspace/user/selectors'

const UserTool = withRouter(
  connect(
    (state) => ({
      currentUser: securitySelectors.currentUser(state),
      workspace: select.workspace(state)
    })
  )(UserToolComponent)
)

export {
  UserTool
}
