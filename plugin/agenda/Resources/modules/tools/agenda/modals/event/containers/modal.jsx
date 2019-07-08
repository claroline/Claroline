import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {
  actions as formActions,
  selectors as formSelect
} from '#/main/app/content/form/store'

import {EventModal as EventModalComponent} from '#/plugin/agenda/tools/agenda/modals/event/components/modal'
import {reducer, selectors} from '#/plugin/agenda/tools/agenda/modals/event/store'

const EventModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      saveEnabled: formSelect.saveEnabled(formSelect.form(state, selectors.STORE_NAME))
    }),
    (dispatch) => ({
      update(prop, value) {
        dispatch(formActions.updateProp(selectors.STORE_NAME, prop, value))
      },
      save(event, isNew = false) {
        if (isNew) {
          dispatch(formActions.saveForm(selectors.STORE_NAME, ['apiv2_event_update', {id: event.id}]))
        } else {
          dispatch(formActions.saveForm(selectors.STORE_NAME, ['apiv2_event_create']))
        }

      }
      /*loadWorkspace(workspace) {
        dispatch(formActions.resetForm(selectors.STORE_NAME, workspace))
      },
      */
    })
  )(EventModalComponent)
)

export {
  EventModal
}
