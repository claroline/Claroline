import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as contextSelectors} from '#/main/app/context/store/selectors'

import {ContextProfile as ContextProfileComponent} from '#/main/app/context/profile/components/main'
import {actions, reducer, selectors} from '#/main/app/context/profile/store'

const ContextProfile = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      path: contextSelectors.path(state),
      currentUser: securitySelectors.currentUser(state)
    }),
    (dispatch) => ({
      reset(user) {
        dispatch(actions.load(user))
      }
    })
  )(ContextProfileComponent)
)

export {
  ContextProfile
}
