import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {actions as listActions, select as listSelect} from '#/main/app/content/list/store'

import {reducer, selectors} from '#/main/community/modals/register/store'
import {RegisterModal as RegisterModalComponent} from '#/main/community/modals/register/components/modal'

const RegisterModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      selectedUsers: listSelect.selectedFull(listSelect.list(state, selectors.USERS_LIST)),
      selectedGroups: listSelect.selectedFull(listSelect.list(state, selectors.GROUPS_LIST))
    }),
    (dispatch) => ({
      resetUsers() {
        dispatch(listActions.resetSelect(selectors.USERS_LIST))
        dispatch(listActions.invalidateData(selectors.USERS_LIST))
      },
      resetGroups() {
        dispatch(listActions.resetSelect(selectors.GROUPS_LIST))
        dispatch(listActions.invalidateData(selectors.GROUPS_LIST))
      }
    })
  )(RegisterModalComponent)
)

export {
  RegisterModal
}
