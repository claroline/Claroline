import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/reducer'
import {actions as formActions} from '#/main/app/content/form/store'

import {MaintenanceModal as MaintenanceModalComponent} from '#/main/app/modals/maintenance/components/modal'
import {reducer, selectors} from '#/main/app/modals/maintenance/store'

const MaintenanceModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    null,
    (dispatch) => ({
      updateProp(prop, value) {
        dispatch(formActions.updateProp(selectors.STORE_NAME, prop, value))
      }
    })
  )(MaintenanceModalComponent)
)

export {
  MaintenanceModal
}
