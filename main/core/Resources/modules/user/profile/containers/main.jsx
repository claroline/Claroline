import {connect} from 'react-redux'
import {withRouter} from '#/main/app/router'

import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as toolSelectors} from  '#/main/core/tool/store'
import {ProfileComponent} from '#/main/core/user/profile/components/main.jsx'
import {selectors} from '#/main/app/content/details/store'
import {selectors as profileSelect} from '#/main/core/user/profile/store/selectors'
import {selectors as select} from '#/main/core/user/profile/store/selectors'
import {actions} from '#/main/core/user/store/actions'

const Profile = withRouter(
  connect(
    (state) => ({
      currentContext: toolSelectors.context(state),
      path: toolSelectors.path(state) + '/profile',
      currentUser: securitySelectors.currentUser(state),
      user: selectors.data(selectors.details(state, select.FORM_NAME)),
      parameters: profileSelect.parameters(state),
      loaded: profileSelect.loaded(state)
    }),
    (dispatch) => ({
      updatePassword(user, password) {
        dispatch(actions.updatePassword(user, password))
      },
      updatePublicUrl(user, publicUrl) {
        dispatch(actions.updatePublicUrl(user, publicUrl, true))
      }
    })
  )(ProfileComponent)
)

export {
  Profile
}
