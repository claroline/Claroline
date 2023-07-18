import {connect} from 'react-redux'

import {actions as formActions} from '#/main/app/content/form/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {AuthenticationTool as AuthenticationComponent} from '#/main/authentication/administration/authentication/components/tool'

const AuthenticationTool =
  connect(
    (state) => ({
      path: toolSelectors.path(state)
    }),
    dispatch => ({
      updateProp(prop, value) {
        dispatch(formActions.updateProp(selectors.FORM_NAME, prop, value))
      }
    })
  )(AuthenticationComponent)

export {
  AuthenticationTool
}
