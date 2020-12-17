import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {
  actions as listActions,
  select as listSelect
} from '#/main/app/content/list/store'

import {reducer, selectors} from '#/plugin/competency/modals/abilities/store'
import {AbilitiesPickerModal as AbilitiesPickerModalComponent} from '#/plugin/competency/modals/abilities/components/modal'

const AbilitiesPickerModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      selected: listSelect.selectedFull(listSelect.list(state, selectors.STORE_NAME))
    }),
    (dispatch) => ({
      resetSelect() {
        dispatch(listActions.resetSelect(selectors.STORE_NAME))
      }
    })
  )(AbilitiesPickerModalComponent)
)

export {
  AbilitiesPickerModal
}
