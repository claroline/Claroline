import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {actions as listActions} from '#/main/app/content/list/store'

import {reducer, selectors} from '#/main/authentication/account/authentication/store'
import {AuthenticationTool as AuthenticationToolComponent}  from '#/main/authentication/account/authentication/components/tool'

const AuthenticationTool = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      currentUser: securitySelectors.currentUser(state)
    }),
    (dispatch) => ({
      invalidateList() {
        dispatch(listActions.invalidateData(selectors.STORE_NAME))
      }
    })
  )(AuthenticationToolComponent)
)

export {
  AuthenticationTool
}
