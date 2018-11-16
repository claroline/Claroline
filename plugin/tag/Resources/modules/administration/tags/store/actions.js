import {API_REQUEST} from '#/main/app/api'
import {actions as formActions} from '#/main/app/content/form/store/actions'

export const actions = {}

actions.openForm = (tagId = null) => {
  if (tagId) {
    return {
      [API_REQUEST]: {
        url: ['apiv2_tag_get', {id: tagId}],
        success: (response, dispatch) => dispatch(formActions.resetForm('tag.form', response, false))
      }
    }
  } else {
    return formActions.resetForm('tag.form', {}, true)
  }
}
