import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool'

import {HomeTab as HomeTabComponent} from '#/plugin/home/tools/home/components/tab'
import {actions, selectors} from '#/plugin/home/tools/home/store'

const HomeTab = connect(
  (state) => ({
    path: toolSelectors.path(state),
    currentContext: toolSelectors.context(state),

    loaded: selectors.loaded(state),
    accessErrors: selectors.accessErrors(state),
    managed: selectors.managed(state),
    currentTab: selectors.currentTab(state),
    currentTabTitle: selectors.currentTabTitle(state)
  }),
  (dispatch) => ({
    open(tab) {
      dispatch(actions.fetchTab(tab))
    },
    dismissRestrictions() {
      dispatch(actions.dismissRestrictions())
    },
    checkAccessCode(tab, code) {
      dispatch(actions.checkAccessCode(tab, code))
    }
  })
)(HomeTabComponent)

export {
  HomeTab
}
