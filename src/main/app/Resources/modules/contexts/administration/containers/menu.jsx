import {connect} from 'react-redux'

import {actions as layoutActions, selectors as layoutSelectors} from '#/main/app/layout/store'

import {AdministrationMenu as AdministrationMenuComponent} from '#/main/app/contexts/administration/components/menu'
import {selectors as contextSelectors} from '#/main/app/context/store'

const AdministrationMenu = connect(
  (state) => ({
    tools: contextSelectors.tools(state),

    maintenance: layoutSelectors.maintenance(state),
    disabled: layoutSelectors.disabled(state)
  }),
  (dispatch) => ({
    // TODO : move in action system
    enableMaintenance(message) {
      return dispatch(layoutActions.enableMaintenance(message))
    },
    disableMaintenance() {
      return dispatch(layoutActions.disableMaintenance())
    }
  })
)(AdministrationMenuComponent)

export {
  AdministrationMenu
}
