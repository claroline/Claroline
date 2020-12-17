import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/reducer'
import {selectors as detailsSelectors} from '#/main/app/content/details/store'
import {selectors as profileSelectors} from '#/main/core/user/profile/store'

import {Tracking as TrackingComponent} from '#/plugin/analytics/user/tracking/components/main'
import {actions, reducer, selectors} from '#/plugin/analytics/user/tracking/store'

const Tracking = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      user: detailsSelectors.data(detailsSelectors.details(state, profileSelectors.FORM_NAME)),
      evaluations: selectors.store(state)
    }),
    (dispatch) => ({
      loadTracking(userId, startDate, endDate) {
        dispatch(actions.loadTracking(userId, startDate, endDate))
      }
    })
  )(TrackingComponent)
)

export {
  Tracking
}
