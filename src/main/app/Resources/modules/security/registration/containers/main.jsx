import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {withReducer} from '#/main/app/store/components/withReducer'
import {selectors as formSelectors} from '#/main/app/content/form/store'

import {RegistrationMain as RegistrationMainComponent} from '#/main/app/security/registration/components/main'
import {actions, reducer, selectors} from '#/main/app/security/registration/store'

const RegistrationMain = withRouter(
  withReducer(selectors.STORE_NAME, reducer)(
    connect(
      (state) => ({
        user: formSelectors.data(formSelectors.form(state, selectors.FORM_NAME)),
        facets: selectors.facets(state),
        allFacetFields: selectors.allFacetFields(state),
        termOfService: selectors.termOfService(state),
        options: selectors.options(state)
      }),
      (dispatch) => ({
        register(user, termOfService, onRegister) {
          dispatch(actions.createUser(user, onRegister))
        },
        fetchRegistrationData() {
          dispatch(actions.fetchRegistrationData())
        }
      })
    )(RegistrationMainComponent)
  )
)

export {
  RegistrationMain
}
