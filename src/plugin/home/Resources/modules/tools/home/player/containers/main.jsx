import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {PlayerMain as PlayerMainComponent} from '#/plugin/home/tools/home/player/components/main'
import {selectors as playerSelectors} from '#/plugin/home/tools/home/player/store'
import {actions} from '#/plugin/home/tools/home/store'

const PlayerMain = withRouter(
  connect(
    (state) => ({
      path: toolSelectors.path(state),
      currentContext: toolSelectors.context(state),

      tabs: playerSelectors.tabs(state),
      currentTab: playerSelectors.currentTab(state),
      currentTabTitle: playerSelectors.currentTabTitle(state)
    }),
    (dispatch) => ({
      setCurrentTab(tab) {
        dispatch(actions.setCurrentTab(tab))
      }
    })
  )(PlayerMainComponent)
)

export {
  PlayerMain
}
