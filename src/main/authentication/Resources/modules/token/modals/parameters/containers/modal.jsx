import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {
  actions as formActions,
  selectors as formSelectors
} from '#/main/app/content/form/store'

import {ParametersModal as ParametersModalComponent} from '#/main/authentication/token/modals/parameters/components/modal'
import {reducer, selectors} from '#/main/authentication/token/modals/parameters/store'

const ParametersModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      isNew: formSelectors.isNew(formSelectors.form(state, selectors.STORE_NAME)),
      data: formSelectors.data(formSelectors.form(state, selectors.STORE_NAME)),
      saveEnabled: formSelectors.saveEnabled(formSelectors.form(state, selectors.STORE_NAME))
    }),
    (dispatch) => ({
      load(token = null) {
        dispatch(formActions.resetForm(selectors.STORE_NAME, token, !token || !token.id))
      },
      save(token, isNew, callback) {
        dispatch(formActions.saveForm(selectors.STORE_NAME, isNew ? ['apiv2_apitoken_create'] : ['apiv2_apitoken_update', {id: token.id}])).then((response) => {
          if (callback) {
            callback(response)
          }
        })
      }
    })
  )(ParametersModalComponent)
)

export {
  ParametersModal
}
