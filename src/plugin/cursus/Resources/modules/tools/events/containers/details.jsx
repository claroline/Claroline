import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {actions, selectors} from '#/plugin/cursus/event/store'
import {EventsDetails as EventsDetailsComponent} from '#/plugin/cursus/tools/events/components/details'

const EventsDetails = connect(
  (state, ownProps) => ({
    path: ownProps.path || toolSelectors.path(state),
    event: selectors.event(state)
  }),
  (dispatch) => ({
    reload(id, force = true) {
      dispatch(actions.open(id, force))
    }
  })
)(EventsDetailsComponent)

export {
  EventsDetails
}
