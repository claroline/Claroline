import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {PlayerMain as PlayerMainComponent} from '#/main/core/tools/home/player/components/main'
import {selectors as playerSelectors} from '#/main/core/tools/home/player/store'
import {actions, selectors} from '#/main/core/tools/home/store'

const PlayerMain = withRouter(
  connect(
    (state) => ({
      path: toolSelectors.path(state),
      currentContext: toolSelectors.context(state),
      editable: selectors.editable(state),
      administration: selectors.administration(state),
      desktopAdmin: selectors.desktopAdmin(state),

      tabs: playerSelectors.tabs(state),
      currentTab: playerSelectors.currentTab(state),
      currentTabTitle: playerSelectors.currentTabTitle(state),
      widgets: playerSelectors.widgets(state)
    }),
    (dispatch) => ({
      setCurrentTab(tab) {
        dispatch(actions.setCurrentTab(tab))
      },
      setAdministration(administration) {
        dispatch(actions.setAdministration(administration))
      },
      fetchTabs(administration) {
        dispatch(actions.fetchTabs(administration))
      }
    })
  )(PlayerMainComponent)
)

export {
  PlayerMain
}
