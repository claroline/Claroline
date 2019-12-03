import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {withReducer} from '#/main/app/store/components/withReducer'

import {AdministrationMenu as AdministrationMenuComponent} from '#/main/app/layout/sections/administration/components/menu'
import {actions as menuActions, selectors as menuSelectors} from '#/main/app/layout/menu/store'
import {actions as layoutActions, selectors as layoutSelectors} from '#/main/app/layout/store'
import {reducer, selectors} from '#/main/app/layout/sections/administration/store'

const AdministrationMenu = withRouter(
  withReducer(selectors.STORE_NAME, reducer)(
    connect(
      (state) => ({
        section: menuSelectors.openedSection(state),
        tools: selectors.tools(state),
        maintenance: layoutSelectors.maintenance(state)
      }),
      (dispatch) => ({
        changeSection(section) {
          dispatch(menuActions.changeSection(section))
        },

        enableMaintenance(message) {
          return dispatch(layoutActions.enableMaintenance(message))
        },
        disableMaintenance() {
          return dispatch(layoutActions.disableMaintenance())
        }
      })
    )(AdministrationMenuComponent)
  )
)

export {
  AdministrationMenu
}
