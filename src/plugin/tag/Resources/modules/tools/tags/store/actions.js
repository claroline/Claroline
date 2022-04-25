import {API_REQUEST} from '#/main/app/api'
import {actions as formActions} from '#/main/app/content/form/store/actions'

import {selectors} from '#/plugin/tag/tools/tags/store/selectors'

export const actions = {}

actions.openForm = (tagId = null) => {
  if (tagId) {
    return {
      [API_REQUEST]: {
        url: ['apiv2_tag_get', {id: tagId}],
        success: (response, dispatch) => dispatch(formActions.resetForm(selectors.STORE_NAME + '.tag.form', response, false))
      }
    }
  } else {
    return formActions.resetForm(selectors.STORE_NAME + '.tag.form', {}, true)
  }
}
