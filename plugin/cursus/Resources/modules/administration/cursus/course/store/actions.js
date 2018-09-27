import {API_REQUEST, url} from '#/main/app/api'
import {actions as formActions} from '#/main/app/content/form/store'
import {actions as listActions} from '#/main/app/content/list/store'

export const actions = {}

actions.open = (formName, defaultProps, id = null) => (dispatch) => {
  if (id) {
    dispatch({
      [API_REQUEST]: {
        url: ['apiv2_cursus_course_get', {id}],
        success: (response, dispatch) => {
          dispatch(formActions.resetForm(formName, response, false))
        }
      }
    })
  } else {
    dispatch(formActions.resetForm(formName, defaultProps, true))
    dispatch(listActions.invalidateData(formName+'.sessions'))
    dispatch(listActions.invalidateData(formName+'.organizations.list'))
  }
}

actions.reset = (formName) => (dispatch) => {
  dispatch(formActions.resetForm(formName, {}, true))
}

actions.addOrganizations = (courseId, organizations) => ({
  [API_REQUEST]: {
    url: url(['apiv2_cursus_course_add_organizations', {id: courseId}], {ids: organizations}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData('courses.current.organizations.list'))
    }
  }
})
