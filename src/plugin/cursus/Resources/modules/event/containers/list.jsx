import {connect} from 'react-redux'

import {actions as listActions} from '#/main/app/content/list/store'
import {EventList as EventListComponent} from '#/plugin/cursus/event/components/list'

const EventList = connect(
  null,
  (dispatch, ownProps) => ({
    invalidate() {
      dispatch(listActions.invalidateData(ownProps.name))
    }
  })
)(EventListComponent)

export {
  EventList
}
