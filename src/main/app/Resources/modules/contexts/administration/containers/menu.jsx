import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {withReducer} from '#/main/app/store/components/withReducer'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {actions as menuActions, selectors as menuSelectors} from '#/main/app/layout/menu/store'
import {actions as layoutActions, selectors as layoutSelectors} from '#/main/app/layout/store'

import {AdministrationMenu as AdministrationMenuComponent} from '#/main/app/contexts/administration/components/menu'
import {selectors as contextSelectors} from '#/main/app/context/store'

const AdministrationMenu = /*withRouter(
  withReducer(selectors.STORE_NAME, reducer)(*/
    connect(
      (state) => ({
        currentUser: securitySelectors.currentUser(state),
        section: menuSelectors.openedSection(state),
        tools: contextSelectors.tools(state),
        maintenance: layoutSelectors.maintenance(state),
        disabled: layoutSelectors.disabled(state)
      }),
      (dispatch) => ({
        changeSection(section) {
          dispatch(menuActions.changeSection(section))
        },

        // TODO : move in action system
        enableMaintenance(message) {
          return dispatch(layoutActions.enableMaintenance(message))
        },
        disableMaintenance() {
          return dispatch(layoutActions.disableMaintenance())
        }
      })
    )(AdministrationMenuComponent)
  /*)
)*/

export {
  AdministrationMenu
}
