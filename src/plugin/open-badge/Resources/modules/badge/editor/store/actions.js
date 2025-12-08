
import {API_REQUEST} from '#/main/app/api'
import {actions as formActions} from '#/main/app/content/form'

import {selectors} from '#/plugin/open-badge/badge/editor/store/selectors'

export const actions = {}

actions.reset = (badgeId, reload = false) => {
  return {
    [API_REQUEST]: {
      url: ['apiv2_badge_get', {id: badgeId}],
      before: (dispatch) => {
        if (!reload) {
          dispatch(formActions.resetForm(selectors.FORM_NAME, {}, false))
        }
      },
      success: (response, dispatch) => {
        dispatch(formActions.resetForm(selectors.FORM_NAME, response, false))
      }
    }
  }
}

actions.update = (value, propPath = null) => {
  if (propPath) {
    return formActions.updateProp(selectors.FORM_NAME, propPath, value)
  }

  return formActions.update(selectors.FORM_NAME, value)
}
