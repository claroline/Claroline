import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {UserTool as UserToolComponent}  from '#/main/core/workspace/user/components/tool'
import {select}  from '#/main/core/workspace/user/selectors'

const UserTool = withRouter(
  connect(
    (state) => ({
      workspace: select.workspace(state)
    })
  )(UserToolComponent)
)

export {
  UserTool
}
