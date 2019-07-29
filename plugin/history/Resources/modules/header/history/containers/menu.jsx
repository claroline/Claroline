import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {HistoryMenu as HistoryMenuComponent} from '#/plugin/history/header/history/components/menu'
import {actions, reducer, selectors} from '#/plugin/history/header/history/store'

const HistoryMenu = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      isAuthenticated: securitySelectors.isAuthenticated(state),
      loaded: selectors.loaded(state),
      results: selectors.results(state)
    }),
    (dispatch) => ({
      getHistory() {
        dispatch(actions.getHistory())
      }
    })
  )(HistoryMenuComponent)
)

export {
  HistoryMenu
}
