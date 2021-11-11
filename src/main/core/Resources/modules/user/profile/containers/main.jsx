import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {withReducer} from '#/main/app/store/reducer'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as toolSelectors} from  '#/main/core/tool/store'
import {selectors as detailSelectors} from '#/main/app/content/details/store'

import {Profile as ProfileComponent} from '#/main/core/user/profile/components/main'
import {selectors, reducer, actions} from '#/main/core/user/profile/store'

const Profile = withRouter(
  withReducer(selectors.STORE_NAME, reducer)(
    connect(
      (state) => ({
        currentContext: toolSelectors.context(state),
        currentUser: securitySelectors.currentUser(state),
        user: detailSelectors.data(detailSelectors.details(state, selectors.FORM_NAME)),
        parameters: selectors.parameters(state),
        loaded: selectors.loaded(state)
      }),
      (dispatch) => ({
        open(username) {
          dispatch(actions.open(username))
        }
      })
    )(ProfileComponent)
  )
)

export {
  Profile
}
