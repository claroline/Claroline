import {connect} from 'react-redux'

import {actions} from '#/main/core/user/store/actions'
import {PublicUrlModal as PublicUrlModalComponent} from '#/main/core/user/modals/public-url/components/public-url'

const PublicUrlModal = connect(
  null,
  (dispatch) => ({
    changeUrl(user, url, redirect, callback) {
      dispatch(actions.updatePublicUrl(user, url, redirect, callback))
    }
  })
)(PublicUrlModalComponent)

export {
  PublicUrlModal
}
