import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

import {selectors, reducer} from '#/plugin/booking/tools/booking/room/modals/parameters/store'
import {RoomParametersModal as RoomParametersModalComponent} from '#/plugin/booking/tools/booking/room/modals/parameters/components/modal'
import {Room as RoomTypes} from '#/plugin/booking/prop-types'

const RoomParametersModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      saveEnabled: formSelectors.saveEnabled(formSelectors.form(state, selectors.STORE_NAME))
    }),
    (dispatch) => ({
      loadRoom(room = null) {
        dispatch(formActions.resetForm(selectors.STORE_NAME, room || RoomTypes.defaultProps, !room))
      },
      saveRoom(eventId = null, onSave = () => true) {
        dispatch(formActions.saveForm(selectors.STORE_NAME, eventId ? ['apiv2_booking_room_update', {id: eventId}] : ['apiv2_booking_room_create'])).then((response) => {
          if (onSave) {
            onSave(response)
          }
        })
      }
    })
  )(RoomParametersModalComponent)
)

export {
  RoomParametersModal
}
