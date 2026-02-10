import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool'

import {HomeTab as HomeTabComponent} from '#/plugin/home/tools/home/components/tab'
import {actions, selectors} from '#/plugin/home/tools/home/store'

const HomeTab = connect(
  (state) => ({
    path: toolSelectors.path(state),
    currentContext: toolSelectors.context(state),

    loaded: selectors.loaded(state),
    error: selectors.error(state),
    currentTab: selectors.currentTab(state)
  }),
  (dispatch) => ({
    open(tab) {
      dispatch(actions.fetchTab(tab))
      dispatch(actions.updateView(tab))
    }
  })
)(HomeTabComponent)

export {
  HomeTab
}
