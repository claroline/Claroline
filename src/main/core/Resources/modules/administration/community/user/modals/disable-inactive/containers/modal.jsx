import {connect} from 'react-redux'

import {actions} from '#/main/core/administration/community/user/modals/disable-inactive/store'
import {DisableInactiveModal as DisableInactiveModalComponent} from '#/main/core/administration/community/user/modals/disable-inactive/components/modal'

const DisableInactiveModal = connect(
  null,
  (dispatch) => ({
    disableInactive(lastLogin) {
      dispatch(actions.disableInactive(lastLogin))
    }
  })
)(DisableInactiveModalComponent)

export {
  DisableInactiveModal
}
