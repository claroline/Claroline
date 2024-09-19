import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/reducer'
import {selectors as formSelectors} from '#/main/app/content/form/store'

import {reducer} from '#/plugin/cursus/session/modals/cancel/store'
import {selectors} from '#/plugin/cursus/session/modals/cancel/store'
import {SessionCancelModal as SessionCancelModalComponent} from '#/plugin/cursus/session/modals/cancel/components/modal'

const SessionCancelModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      formData: formSelectors.data(formSelectors.form(state, selectors.STORE_NAME))
    })
  )(SessionCancelModalComponent)
)

export {
  SessionCancelModal
}
