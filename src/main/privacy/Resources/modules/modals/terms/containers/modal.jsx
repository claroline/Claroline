import {connect} from 'react-redux'
import {withReducer} from '#/main/app/store/components/withReducer'
import {TermsOfServiceModal as ThermsOfServiceModalComponent} from '#/main/privacy/modals/terms/components/modal'
import {selectors, reducer} from '#/main/privacy/modals/terms/store'
import {selectors as formSelectors} from '#/main/app/content/form/store'

const TermsOfServiceModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      formData:
        formSelectors.data(formSelectors.form(state, selectors.STORE_NAME)),
    })
  )(ThermsOfServiceModalComponent)
)

export {TermsOfServiceModal}
