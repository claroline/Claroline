import {connect} from 'react-redux'

import {actions as listActions} from '#/main/app/content/list/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors} from '#/main/authentication/administration/authentication/store'
import {AuthenticationIps as AuthenticationIpsComponent}  from '#/main/authentication/administration/authentication/components/ips'

const AuthenticationIps = connect(
  (state) => ({
    path: toolSelectors.path(state)
  }),
  (dispatch) => ({
    invalidateList() {
      dispatch(listActions.invalidateData(selectors.STORE_NAME+'.ips'))
    }
  })
)(AuthenticationIpsComponent)

export {
  AuthenticationIps
}
