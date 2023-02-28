import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/reducer'
import {selectors as formSelectors} from '#/main/app/content/form/store'

import {RegistrationModal as RegistrationModalComponent} from '#/plugin/cursus/course/modals/registration/components/modal'
import {selectors, reducer} from '#/plugin/cursus/course/modals/registration/store'

const RegistrationModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      formData: formSelectors.data(formSelectors.form(state, selectors.STORE_NAME))
    }),
    (dispatch) => ({
      save() {

      }
    })
  )(RegistrationModalComponent)
)

export {
  RegistrationModal
}
