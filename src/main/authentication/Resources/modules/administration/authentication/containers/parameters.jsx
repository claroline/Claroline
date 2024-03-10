import {connect} from 'react-redux'

import {actions as formActions} from '#/main/app/content/form/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {AuthenticationParameters as AuthenticationParametersComponent} from '#/main/authentication/administration/authentication/components/parameters'
import {selectors} from '#/main/authentication/administration/authentication/store'

const AuthenticationParameters =
  connect(
    (state) => ({
      path: toolSelectors.path(state)
    }),
    (dispatch) => ({
      update(prop, value) {
        dispatch(formActions.updateProp(selectors.STORE_NAME, prop, value))
      }
    })
  )(AuthenticationParametersComponent)

export {
  AuthenticationParameters
}
