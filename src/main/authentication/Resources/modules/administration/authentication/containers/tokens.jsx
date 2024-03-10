import {connect} from 'react-redux'

import {actions as listActions} from '#/main/app/content/list/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors} from '#/main/authentication/administration/authentication/store'
import {AuthenticationTokens as AuthenticationTokensComponent}  from '#/main/authentication/administration/authentication/components/tokens'

const AuthenticationTokens = connect(
  (state) => ({
    path: toolSelectors.path(state)
  }),
  (dispatch) => ({
    invalidateList() {
      dispatch(listActions.invalidateData(selectors.STORE_NAME+'.tokens'))
    }
  })
)(AuthenticationTokensComponent)

export {
  AuthenticationTokens
}
