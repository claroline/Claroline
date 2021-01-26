import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {BBBTool as BBBToolComponent} from '#/integration/big-blue-button/integration/bbb/components/tool'
import {actions, reducer, selectors} from '#/integration/big-blue-button/integration/bbb/store'

const BBBTool = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      path: toolSelectors.path(state),
      loaded: selectors.loaded(state),
      maxMeetings: selectors.maxMeetings(state),
      maxMeetingParticipants: selectors.maxMeetingParticipants(state),
      maxParticipants: selectors.maxParticipants(state),
      activeMeetings: selectors.activeMeetings(state),
      activeMeetingsCount: selectors.activeMeetingsCount(state),
      participantsCount: selectors.participantsCount(state),
      servers: selectors.servers(state),
      allowRecords: selectors.allowRecords(state)
    }),
    (dispatch) => ({
      loadInfo() {
        dispatch(actions.fetchInfo())
      },
      endMeetings(meetingIds) {
        dispatch(actions.endMeetings(meetingIds))
      },
      syncRecordings() {
        dispatch(actions.syncRecordings())
      }
    })
  )(BBBToolComponent)
)

export {
  BBBTool
}
