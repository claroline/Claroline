import {connect} from 'react-redux'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {selectors as baseSelectors} from '#/integration/big-blue-button/resources/bbb/store'

import {actions, selectors} from '#/integration/big-blue-button/resources/bbb/records/store'
import {Records as RecordsComponent} from '#/integration/big-blue-button/resources/bbb/records/components/records'

const Records = connect(
  (state) => ({
    path: resourceSelectors.path(state),
    bbb: baseSelectors.bbb(state),
    canEdit: baseSelectors.canEdit(state),
    recordings: selectors.recordings(state)
  }),
  (dispatch) => ({
    deleteRecording(meetingId, recordId) {
      dispatch(actions.deleteRecording(meetingId, recordId))
    }
  })
)(RecordsComponent)

export {
  Records
}