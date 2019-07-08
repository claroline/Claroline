import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {
  actions as listActions,
  select as listSelect
} from '#/main/app/content/list/store'

import {reducer, selectors} from '#/main/core/modals/groups/store'
import {GroupsModal as GroupsModalComponent} from '#/main/core/modals/groups/components/modal'

const GroupsModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      selected: listSelect.selectedFull(listSelect.list(state, selectors.STORE_NAME))
    }),
    (dispatch) => ({
      resetSelect() {
        dispatch(listActions.resetSelect(selectors.STORE_NAME))
      }
    })
  )(GroupsModalComponent)
)

export {
  GroupsModal
}