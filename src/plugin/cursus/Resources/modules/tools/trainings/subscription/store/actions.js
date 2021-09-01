import {API_REQUEST, url} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'

export const UPDATE_SUBSCRIPTION_STATUS = 'UPDATE_SUBSCRIPTION_STATUS'
export const SET_STATISTICS = 'SET_STATISTICS'

export const actions = {}

actions.updateSubscriptionStatus = makeActionCreator(UPDATE_SUBSCRIPTION_STATUS, 'subscription')
actions.setStatistics = makeActionCreator(SET_STATISTICS, 'statistics')

actions.getStatistics = (id) => (dispatch) => {
  return dispatch({
    [API_REQUEST]: {
      url: url(['apiv2_cursus_quota_statistics', {id}]),
      silent: true,
      success: (data) => {
        dispatch(actions.setStatistics(data))
      }
    }
  })
}

actions.setSubscriptionStatus = (quotaId, subscriptionId, status, remark) => (dispatch) => {
  return dispatch({
    [API_REQUEST]: {
      url: url(['apiv2_cursus_subscription_status', {id:quotaId, sid:subscriptionId}], {status, remark}),
      request: {
        method: 'PATCH'
      },
      silent: true,
      success: () => {
        dispatch(actions.updateSubscriptionStatus({id:subscriptionId, status, remark}))
        dispatch(actions.getStatistics(quotaId))
      }
    }
  })
}