import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {
  actions as listActions,
  select as listSelect
} from '#/main/app/content/list/store'

import {select as workspaceSelect} from '#/main/core/workspace/selectors'

import {reducer, selectors} from '#/plugin/planned-notification/modals/notifications/store'
import {NotificationsPickerModal as NotificationsPickerModalComponent} from '#/plugin/planned-notification/modals/notifications/components/modal'

const NotificationsPickerModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      selected: listSelect.selectedFull(listSelect.list(state, selectors.STORE_NAME)),
      workspaceId: workspaceSelect.workspace(state).uuid
    }),
    (dispatch) => ({
      resetSelect() {
        dispatch(listActions.resetSelect(selectors.STORE_NAME))
      }
    })
  )(NotificationsPickerModalComponent)
)

export {
  NotificationsPickerModal
}