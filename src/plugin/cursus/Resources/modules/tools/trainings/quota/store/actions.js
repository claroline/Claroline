import isEmpty from 'lodash/isEmpty'

import {API_REQUEST, url} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'
import {actions as formActions} from '#/main/app/content/form/store/actions'

import {selectors} from '#/plugin/cursus/tools/trainings/quota/store/selectors'

export const LOAD_QUOTA = 'LOAD_QUOTA'

export const actions = {}

actions.loadQuota = makeActionCreator(LOAD_QUOTA, 'quota')

actions.open = (id, force = false) => (dispatch, getState) => {
  const currentQuota = selectors.quota(getState())
  if (force || isEmpty(currentQuota) || currentQuota.id !== id) {
    return dispatch({
      [API_REQUEST]: {
        url: ['apiv2_cursus_quota_open', {id}],
        silent: true,
        before: () => dispatch(actions.loadQuota(null)),
        success: (data) => dispatch(actions.loadQuota(data.quota))
      }
    })
  }
}

actions.openForm = (id = null, defaultProps = {}) => (dispatch) => {
  if (!id) {
    return dispatch(formActions.resetForm(selectors.FORM_NAME, defaultProps, true))
  }

  return dispatch({
    [API_REQUEST]: {
      url: url(['apiv2_cursus_quota_find'], {filters: {uuid: id}}),
      silent: true,
      success: (data) => dispatch(formActions.resetForm(selectors.FORM_NAME, data))
    }
  })
}