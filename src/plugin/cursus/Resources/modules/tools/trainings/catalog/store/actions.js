import {API_REQUEST} from '#/main/app/api'
import {actions as formActions} from '#/main/app/content/form/store/actions'

import {selectors} from '#/plugin/cursus/tools/trainings/catalog/store/selectors'

export const actions = {}

actions.openForm = (courseSlug = null, defaultProps = {}) => (dispatch) => {
  if (!courseSlug) {
    return dispatch(formActions.resetForm(selectors.FORM_NAME, defaultProps, true))
  }
  return dispatch({
    [API_REQUEST]: {
      url: ['apiv2_cursus_course_get', {field: 'slug', id: courseSlug}],
      silent: true,
      before: () => dispatch(formActions.resetForm(selectors.FORM_NAME, null, false)),
      success: (data) => dispatch(formActions.resetForm(selectors.FORM_NAME, data))
    }
  })
}
