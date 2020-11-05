import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/reducer'

import {TermsOfServiceModal as TermsOfServiceModalComponent} from '#/main/app/modals/terms-of-service/components/modal'
import {actions, reducer, selectors} from '#/main/app/modals/terms-of-service/store'

const TermsOfServiceModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      loaded: selectors.loaded(state),
      content: selectors.content(state)
    }),
    (dispatch) => ({
      fetch() {
        dispatch(actions.fetch())
      }
    })
  )(TermsOfServiceModalComponent)
)

export {
  TermsOfServiceModal
}
