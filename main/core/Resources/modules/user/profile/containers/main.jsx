import {connect} from 'react-redux'
import merge from 'lodash/merge'
import mergeWith from 'lodash/mergeWith'

import {withRouter} from '#/main/app/router'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as toolSelectors} from  '#/main/core/tool/store'

import {ProfileComponent} from '#/main/core/user/profile/components/main'
import {selectors} from '#/main/app/content/details/store'
import {selectors as profileSelect} from '#/main/core/user/profile/store/selectors'
import {selectors as select} from '#/main/core/user/profile/store/selectors'

const Profile = withRouter(
  connect(
    (state) => {
      // retrieve tool rights and pass it to the user page
      const user = selectors.data(selectors.details(state, select.FORM_NAME))
      const toolPerms = toolSelectors.permissions(state)
      
      return ({
        currentContext: toolSelectors.context(state),
        path: toolSelectors.path(state) + '/profile',
        currentUser: securitySelectors.currentUser(state),
        user: merge({}, user, {
          permissions: mergeWith({}, user.permissions || {}, toolPerms, (objValue, srcValue) => objValue || srcValue)
        }),
        parameters: profileSelect.parameters(state),
        loaded: profileSelect.loaded(state)
      })
    }
  )(ProfileComponent)
)

export {
  Profile
}
