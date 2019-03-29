import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {
  actions as listActions,
  select as listSelect
} from '#/main/app/content/list/store'

import {reducer, selectors} from '#/main/core/modals/groups/store'
import {ImportModal as ImportModalComponent} from '#/plugin/exo/items/modals/import/components/modal'

const ImportModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      name: selectors.STORE_NAME,
      selected: listSelect.selectedFull(listSelect.list(state, selectors.STORE_NAME))
    }),
    (dispatch) => ({
      resetSelect() {
        dispatch(listActions.resetSelect(selectors.STORE_NAME))
      }
    })
  )(ImportModalComponent)
)

export {
  ImportModal
}