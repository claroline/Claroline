import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {withReducer} from '#/main/app/store/components/withReducer'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

import {ProfileEdit as ProfileEditComponent} from '#/main/community/profile/components/edit'
import {reducer, selectors} from '#/main/community/profile/store'

const ProfileEdit = withRouter(
  withReducer(selectors.STORE_NAME, reducer)(
    connect(
      (state, ownProps) => ({
        currentUser: securitySelectors.currentUser(state),
        isNew: formSelectors.isNew(formSelectors.form(state, ownProps.name)),
        facet: selectors.currentFacet(state),
        parameters: selectors.parameters(state),
        allFields: selectors.allFields(state)
      }),
      (dispatch, ownProps) => ({
        updateProp(prop, value) {
          dispatch(formActions.updateProp(ownProps.name, prop, value))
        },
        save(targetUrl) {
          return dispatch(formActions.save(ownProps.name, targetUrl))
        }
      })
    )(ProfileEditComponent)
  )
)

export {
  ProfileEdit
}
