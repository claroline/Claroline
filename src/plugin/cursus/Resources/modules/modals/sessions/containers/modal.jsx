import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {actions as listActions, selectors as listSelectors} from '#/main/app/content/list/store'

import {reducer, selectors} from '#/plugin/cursus/modals/sessions/store'
import {SessionsModal as SessionsModalComponent} from '#/plugin/cursus/modals/sessions/components/modal'

const SessionsModal = withReducer(selectors.STORE_NAME, reducer)(
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
  )(SessionsModalComponent)
)

export {
  SessionsModal
}
