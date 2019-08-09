import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {withReducer} from '#/main/app/store/components/withReducer'
import {selectors as configSelectors} from '#/main/app/config/store'

import {DesktopMenu as DesktopMenuComponent} from '#/main/app/layout/sections/desktop/components/menu'
import {actions as menuActions, selectors as menuSelectors} from '#/main/app/layout/menu/store'
import {reducer, selectors} from '#/main/app/layout/sections/desktop/store'

const DesktopMenu = withRouter(
  withReducer(selectors.STORE_NAME, reducer)(
    connect(
      (state) => ({
        showProgression: configSelectors.param(state, 'desktop.showProgression'),
        section: menuSelectors.openedSection(state),
        tools: selectors.tools(state)
      }),
      (dispatch) => ({
        changeSection(section) {
          dispatch(menuActions.changeSection(section))
        }
      })
    )(DesktopMenuComponent)
  )
)

export {
  DesktopMenu
}
