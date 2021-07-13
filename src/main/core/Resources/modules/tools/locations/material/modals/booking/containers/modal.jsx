import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

import {selectors, reducer} from '#/main/core/tools/locations/material/modals/booking/store'
import {MaterialBookingModal as MaterialBookingModalComponent} from '#/main/core/tools/locations/material/modals/booking/components/modal'
import {MaterialBooking as MaterialBookingTypes} from '#/main/core/tools/locations/prop-types'

const MaterialBookingModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      saveEnabled: formSelectors.saveEnabled(formSelectors.form(state, selectors.STORE_NAME))
    }),
    (dispatch) => ({
      loadBooking(booking = null) {
        dispatch(formActions.resetForm(selectors.STORE_NAME, booking || MaterialBookingTypes.defaultProps, !booking))
      },
      saveBooking(materialId, bookingId = null, onSave = () => true) {
        dispatch(formActions.saveForm(selectors.STORE_NAME, bookingId ?
          ['apiv2_booking_material_update', {material: materialId, id: bookingId}] :
          ['apiv2_booking_material_book', {material: materialId}]
        )).then((response) => {
          if (onSave) {
            onSave(response)
          }
        })
      }
    })
  )(MaterialBookingModalComponent)
)

export {
  MaterialBookingModal
}
