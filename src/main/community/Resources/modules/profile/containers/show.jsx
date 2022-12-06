import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {withReducer} from '#/main/app/store/reducer'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {ProfileShow as ProfileShowComponent} from '#/main/community/profile/components/show'
import {selectors, reducer} from '#/main/community/profile/store'

const ProfileShow = withRouter(
  withReducer(selectors.STORE_NAME, reducer)(
    connect(
      (state) => ({
        currentUser: securitySelectors.currentUser(state),
        facet: selectors.currentFacet(state),
        parameters: selectors.parameters(state),
        allFields: selectors.allFields(state)
      })
    )(ProfileShowComponent)
  )
)

export {
  ProfileShow
}
