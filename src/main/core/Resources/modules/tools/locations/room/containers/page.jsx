import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {actions as listActions} from '#/main/app/content/list/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {RoomPage as RoomPageComponent} from '#/main/core/tools/locations/room/components/page'
import {selectors} from '#/main/core/tools/locations/room/store'

const RoomPage = connect(
  (state) => ({
    path: toolSelectors.path(state),
    currentContext: toolSelectors.context(state),
    editable: hasPermission('edit', toolSelectors.toolData(state)),
    bookable: hasPermission('book', toolSelectors.toolData(state))
  }),
  (dispatch) => ({
    invalidateBookings() {
      dispatch(listActions.invalidateData(selectors.FORM_NAME+'.bookings'))
    }
  })
)(RoomPageComponent)

export {
  RoomPage
}
