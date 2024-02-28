import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {actions as listActions, selectors as listSelectors} from '#/main/app/content/list/store'

import {reducer, selectors} from '#/plugin/cursus/modals/events/store'
import {EventsModal as EventsModalComponent} from '#/plugin/cursus/modals/events/components/modal'

const EventsModal = withReducer(selectors.STORE_NAME, reducer)(
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
  )(EventsModalComponent)
)

export {
  EventsModal
}
