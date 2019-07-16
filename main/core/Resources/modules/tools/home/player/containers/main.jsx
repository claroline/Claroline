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
      currentContext: selectors.context(state),
      editable: selectors.editable(state),
      tabs: playerSelectors.tabs(state),
      currentTab: selectors.currentTab(state),
      currentTabTitle: selectors.currentTabTitle(state),
      widgets: selectors.widgets(state)
    }),
    (dispatch) => ({
      setCurrentTab(tab){
        dispatch(actions.setCurrentTab(tab))
      }
    })
  )(PlayerMainComponent)
)

export {
  PlayerMain
}
