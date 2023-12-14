import {connect} from 'react-redux'

import {actions as formActions} from '#/main/app/content/form/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {AuthenticationTool as AuthenticationComponent} from '#/main/authentication/administration/authentication/components/tool'
import {selectors} from '#/main/authentication/administration/authentication/store'

const AuthenticationTool =
  connect(
    (state) => ({
      path: toolSelectors.path(state)
    }),
    (dispatch) => ({
      update(prop, value) {
        dispatch(formActions.updateProp(selectors.STORE_NAME, prop, value))
      }
    })
  )(AuthenticationComponent)

export {
  AuthenticationTool
}
