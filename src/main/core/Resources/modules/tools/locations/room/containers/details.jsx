import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {actions as listActions} from '#/main/app/content/list/store'
import {selectors as detailsSelectors} from '#/main/app/content/details/store'

import {RoomDetails as RoomDetailsComponent} from '#/main/core/tools/locations/room/components/details'
import {selectors} from '#/main/core/tools/locations/room/store'

const RoomDetails = connect(
  (state) => ({
    path: toolSelectors.path(state),
    room: detailsSelectors.data(detailsSelectors.details(state, selectors.FORM_NAME))
  }),
  (dispatch) => ({
    invalidateBookings() {
      dispatch(listActions.invalidateData(selectors.FORM_NAME+'.bookings'))
    }
  })
)(RoomDetailsComponent)

export {
  RoomDetails
}
