import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {
  actions as listActions,
  select as listSelect
} from '#/main/app/content/list/store'

import {reducer, selectors} from '#/main/community/modals/groups/store'
import {GroupsModal as GroupsModalComponent} from '#/main/community/modals/groups/components/modal'

const GroupsModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      selected: listSelect.selectedFull(listSelect.list(state, selectors.STORE_NAME))
    }),
    (dispatch) => ({
      resetFilters(filters) {
        dispatch(listActions.resetFilters(selectors.STORE_NAME, filters))
      },
      reset() {
        dispatch(listActions.resetSelect(selectors.STORE_NAME))
        dispatch(listActions.invalidateData(selectors.STORE_NAME))
      }
    })
  )(GroupsModalComponent)
)

export {
  GroupsModal
}