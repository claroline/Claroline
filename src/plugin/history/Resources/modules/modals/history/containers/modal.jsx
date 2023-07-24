import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {HistoryModal as HistoryModalComponent} from '#/plugin/history/modals/history/components/modal'
import {actions, reducer, selectors} from '#/plugin/history/modals/history/store'

const HistoryModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      loaded: selectors.loaded(state),
      results: selectors.results(state)
    }),
    (dispatch) => ({
      getHistory() {
        dispatch(actions.getHistory())
      }
    })
  )(HistoryModalComponent)
)

export {
  HistoryModal
}
