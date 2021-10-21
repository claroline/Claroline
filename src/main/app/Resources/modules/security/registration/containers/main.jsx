import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {withReducer} from '#/main/app/store/components/withReducer'
import {trans} from '#/main/app/intl/translation'

import {MODAL_CONFIRM} from '#/main/app/modals/confirm'
import {actions as modalActions} from '#/main/app/overlays/modal/store'
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
        options: selectors.options(state),
        workspaces: selectors.workspaces(state),
        defaultWorkspaces: selectors.defaultWorkspaces(state)
      }),
      (dispatch) => ({
        register(user, termOfService, onRegister) {
          if (termOfService) {
            dispatch(modalActions.showModal(MODAL_CONFIRM, {
              icon: 'fa fa-fw fa-copyright',
              title: trans('terms_of_service'),
              question: termOfService,
              isHtml: true,
              confirmButtonText: trans('accept_terms_of_service'),
              handleConfirm: () => {
                // todo : set acceptedTerms flag
                dispatch(actions.createUser(user, onRegister))
              }
            }))
          } else {
            // create new account
            dispatch(actions.createUser(user, onRegister))
          }
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
