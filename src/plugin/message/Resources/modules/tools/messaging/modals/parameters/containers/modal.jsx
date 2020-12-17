import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {selectors as formSelectors} from '#/main/app/content/form/store'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {ParametersModal as ParametersModalComponent} from '#/plugin/message/tools/messaging/modals/parameters/components/modal'
import {actions, reducer, selectors} from '#/plugin/message/tools/messaging/modals/parameters/store'

const ParametersModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      currentUser: securitySelectors.currentUser(state),
      mailNotified: formSelectors.data(formSelectors.form(state, selectors.STORE_NAME)).mailNotified || false,
      saveEnabled: formSelectors.saveEnabled(formSelectors.form(state, selectors.STORE_NAME))
    }),
    (dispatch) => ({
      save(user, mailNotified) {
        dispatch(actions.setMailNotification(user, mailNotified))
      }
    })
  )(ParametersModalComponent)
)

export {
  ParametersModal
}
