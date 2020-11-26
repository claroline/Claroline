import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {actions as formActions} from '#/main/app/content/form/store/actions'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {actions, selectors} from '#/integration/big-blue-button/resources/bbb/store'
import {BBBResource as BBBResourceComponent} from '#/integration/big-blue-button/resources/bbb/components/resource'

const BBBResource = withRouter(
  connect(
    (state) => ({
      path: resourceSelectors.path(state),
      bbb: selectors.bbb(state),
      canEdit: selectors.canEdit(state),
      allowRecords: selectors.allowRecords(state)
    }),
    (dispatch) => ({
      resetForm(formData = null) {
        dispatch(formActions.resetForm(selectors.STORE_NAME+'.bbbForm', formData))
      },
      endMeeting(bbbId) {
        dispatch(actions.endMeeting(bbbId))
      }
    })
  )(BBBResourceComponent)
)

export {
  BBBResource
}
