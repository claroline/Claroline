import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {
  actions as listActions,
  selectors as listSelectors
} from '#/main/app/content/list/store'

import {reducer, selectors} from '#/main/community/modals/roles/store'
import {RolesModal as RolesModalComponent} from '#/main/community/modals/roles/components/modal'

const RolesModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      selected: listSelectors.selectedFull(listSelectors.list(state, selectors.STORE_NAME))
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
  )(RolesModalComponent)
)

export {
  RolesModal
}
