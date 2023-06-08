import {connect} from 'react-redux'
import {withReducer} from '#/main/app/store/components/withReducer'
import {TermsModal as ThermsModalComponent} from '#/main/privacy/modals/terms/components/modal'
import {selectors, reducer} from '#/main/privacy/modals/terms/store'

const TermsModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      parameters: selectors.store(state)
    })
  )(ThermsModalComponent)
)

export {TermsModal}