import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {actions as listActions} from '#/main/app/content/list/store'
import {selectors as detailsSelectors} from '#/main/app/content/details/store'

import {MaterialDetails as MaterialDetailsComponent} from '#/plugin/booking/tools/booking/material/components/details'
import {selectors} from '#/plugin/booking/tools/booking/material/store'

const MaterialDetails = connect(
  (state) => ({
    path: toolSelectors.path(state),
    material: detailsSelectors.data(detailsSelectors.details(state, selectors.FORM_NAME))
  }),
  (dispatch) => ({
    invalidateBookings() {
      dispatch(listActions.invalidateData(selectors.FORM_NAME+'.bookings'))
    }
  })
)(MaterialDetailsComponent)

export {
  MaterialDetails
}
