import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {PlayerMain as PlayerMainComponent} from '#/plugin/home/tools/home/player/components/main'
import {actions as playerActions, selectors as playerSelectors} from '#/plugin/home/tools/home/player/store'
import {actions} from '#/plugin/home/tools/home/store'

const PlayerMain = withRouter(
  connect(
    (state) => ({
      path: toolSelectors.path(state),
      currentContext: toolSelectors.context(state),

      tabs: playerSelectors.tabs(state),
      loaded: playerSelectors.loaded(state),
      accessErrors: playerSelectors.accessErrors(state),
      managed: playerSelectors.managed(state),
      currentTab: playerSelectors.currentTab(state),
      currentTabTitle: playerSelectors.currentTabTitle(state)
    }),
    (dispatch) => ({
      open(tab) {
        dispatch(playerActions.fetchTab(tab))
      },
      setCurrentTab(tab) {
        dispatch(actions.setCurrentTab(tab))
      },
      dismissRestrictions() {
        dispatch(playerActions.dismissRestrictions())
      },
      checkAccessCode(tab, code) {
        dispatch(playerActions.checkAccessCode(tab, code))
      }
    })
  )(PlayerMainComponent)
)

export {
  PlayerMain
}
