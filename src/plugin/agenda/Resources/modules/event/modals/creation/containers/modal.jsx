import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {actions as formActions} from '#/main/app/content/form/store/actions'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {EventCreationModal as EventCreationModalComponent} from '#/plugin/agenda/event/modals/creation/components/modal'
import {actions, reducer, selectors} from '#/plugin/agenda/event/modals/creation/store'

const EventCreationModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      currentUser: securitySelectors.currentUser(state),
      currentContext: toolSelectors.context(state),
      tab: selectors.tab(state),
      saveEnabled: selectors.saveEnabled(state)
    }),
    (dispatch) => ({
      startCreation(contextType, type, currentUser) {
        dispatch(actions.startCreation(contextType, type, currentUser))
      },
      update(field, value) {
        dispatch(formActions.updateProp(selectors.STORE_NAME, field, value))
      },
      setErrors(errors) {
        dispatch(formActions.setErrors(selectors.STORE_NAME, errors))
      },
      reset() {
        dispatch(actions.reset())
      }
    })
  )(EventCreationModalComponent)
)

export {
  EventCreationModal
}
