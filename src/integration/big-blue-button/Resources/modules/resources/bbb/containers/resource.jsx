import {connect} from 'react-redux'
import {withReducer} from '#/main/app/store/reducer'

import {actions as formActions} from '#/main/app/content/form/store/actions'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {actions, reducer, selectors} from '#/integration/big-blue-button/resources/bbb/store'
import {BBBResource as BBBResourceComponent} from '#/integration/big-blue-button/resources/bbb/components/resource'

const BBBResource = withReducer(selectors.STORE_NAME, reducer)(
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
