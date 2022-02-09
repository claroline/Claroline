import {connect} from 'react-redux'

import {actions} from '#/main/core/administration/community/user/modals/disable-inactive/store'
import {DisableInactiveModal as DisableInactiveModalComponent} from '#/main/core/administration/community/user/modals/disable-inactive/components/modal'

const DisableInactiveModal = connect(
  null,
  (dispatch) => ({
    disableInactive(lastActivity) {
      dispatch(actions.disableInactive(lastActivity))
    }
  })
)(DisableInactiveModalComponent)

export {
  DisableInactiveModal
}
