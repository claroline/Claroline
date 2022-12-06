import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {ProfileMain as ProfileMainComponent} from '#/main/community/account/profile/components/main'

import {actions, reducer, selectors} from '#/main/community/account/profile/store'

const ProfileMain = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      currentUser: securitySelectors.currentUser(state)
    }),
    (dispatch) => ({
      reset(user) {
        dispatch(actions.load(user))
      }
    })
  )(ProfileMainComponent)
)

export {
  ProfileMain
}
