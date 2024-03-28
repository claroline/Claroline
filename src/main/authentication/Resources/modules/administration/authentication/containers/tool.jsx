import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {AuthenticationTool as AuthenticationComponent} from '#/main/authentication/administration/authentication/components/tool'
import {reducer, selectors} from '#/main/authentication/administration/authentication/store'
import {withReducer} from '#/main/app/store/reducer'

const AuthenticationTool = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      path: toolSelectors.path(state)
    })
  )(AuthenticationComponent)
)

export {
  AuthenticationTool
}
