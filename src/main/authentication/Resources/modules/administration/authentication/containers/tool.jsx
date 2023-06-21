import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {AuthenticationTool as AuthenticationComponent} from '#/main/authentication/administration/authentication/components/tool'

const AuthenticationTool =
  connect(
    (state) => ({
      path: toolSelectors.path(state),
    })
  )(AuthenticationComponent)

export {
  AuthenticationTool
}
