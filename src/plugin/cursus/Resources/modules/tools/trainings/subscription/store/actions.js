import {API_REQUEST, url} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'

export const UPDATE_SUBSCRIPTION_STATUS = 'UPDATE_SUBSCRIPTION_STATUS'

export const actions = {}

actions.updateSubscriptionStatus = makeActionCreator(UPDATE_SUBSCRIPTION_STATUS, 'subscription')

actions.setSubscriptionStatus = (id, status) => (dispatch) => {
  return dispatch({
    [API_REQUEST]: {
      url: url(['apiv2_cursus_subscription_status', {id}], {status}),
      request: {
        method: 'PATCH'
      },
      silent: true,
      success: () => {
        dispatch(actions.updateSubscriptionStatus({id, status}))
      }
    }
  })
}