import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {withReducer} from '#/main/app/store/components/withReducer'

import {DesktopMenu as DesktopMenuComponent} from '#/main/app/layout/sections/desktop/components/menu'
import {actions as menuActions, selectors as menuSelectors} from '#/main/app/layout/menu/store'
import {actions, reducer, selectors} from '#/main/app/layout/sections/desktop/store'

const DesktopMenu = withRouter(
  withReducer(selectors.STORE_NAME, reducer)(
    connect(
      (state) => ({
        section: menuSelectors.openedSection(state),
        tools: selectors.tools(state),
        historyLoaded: selectors.historyLoaded(state),
        historyResults: selectors.historyResults(state)
      }),
      (dispatch) => ({
        changeSection(section) {
          dispatch(menuActions.changeSection(section))
        },
        getHistory() {
          dispatch(actions.getHistory())
        }
      })
    )(DesktopMenuComponent)
  )
)

export {
  DesktopMenu
}
