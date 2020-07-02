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
      activeMeetingsCount: selectors.activeMeetingsCount(state),
      participantsCount: selectors.participantsCount(state),
      meetings: selectors.meetings(state)
    }),
    (dispatch) => ({
      loadMeetings() {
        dispatch(actions.fetchMeetings())
      },
      endMeeting(meetingId) {
        dispatch(actions.endMeeting(meetingId))
      }
    })
  )(BBBToolComponent)
)

export {
  BBBTool
}
