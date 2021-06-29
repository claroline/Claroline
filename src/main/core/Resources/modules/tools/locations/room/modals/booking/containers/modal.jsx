import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

import {selectors, reducer} from '#/main/core/tools/locations/room/modals/booking/store'
import {RoomBookingModal as RoomBookingModalComponent} from '#/main/core/tools/locations/room/modals/booking/components/modal'
import {RoomBooking as RoomBookingTypes} from '#/main/core/tools/locations/prop-types'

const RoomBookingModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      saveEnabled: formSelectors.saveEnabled(formSelectors.form(state, selectors.STORE_NAME))
    }),
    (dispatch) => ({
      loadBooking(booking = null) {
        dispatch(formActions.resetForm(selectors.STORE_NAME, booking || RoomBookingTypes.defaultProps, !booking))
      },
      saveBooking(roomId, bookingId = null, onSave = () => true) {
        dispatch(formActions.saveForm(selectors.STORE_NAME, bookingId ?
          ['apiv2_booking_room_update', {room: roomId, id: bookingId}] :
          ['apiv2_booking_room_book', {room: roomId}]
        )).then((response) => {
          if (onSave) {
            onSave(response)
          }
        })
      }
    })
  )(RoomBookingModalComponent)
)

export {
  RoomBookingModal
}
