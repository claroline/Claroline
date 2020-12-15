import {API_REQUEST} from '#/main/app/api'
import {actions as formActions} from '#/main/app/content/form/store'
import {actions as listActions} from '#/main/app/content/list/store'

export const actions = {}

actions.open = (formName, defaultProps, id = null) => (dispatch) => {
  if (id) {
    dispatch({
      [API_REQUEST]: {
        url: ['apiv2_competency_get', {id}],
        success: (response, dispatch) => {
          dispatch(formActions.resetForm(formName, response, false))
        }
      }
    })
  } else {
    dispatch(formActions.resetForm(formName, defaultProps, true))
  }
}

actions.reset = (formName) => (dispatch) => {
  dispatch(formActions.resetForm(formName, {}, true))
}

actions.loadCurrent = (storeName, id) => (dispatch) => {
  dispatch(listActions.fetchData(storeName, ['apiv2_competency_tree_list', {id}]))
}

actions.resetCurrent = (storeName) => (dispatch) => {
  dispatch(listActions.loadData(storeName, [], 0))
}

actions.openCompetencyAbility = (formName, defaultProps, id = null) => (dispatch) => {
  if (id) {
    dispatch({
      [API_REQUEST]: {
        url: ['apiv2_competency_ability_get', {id}],
        success: (response, dispatch) => {
          dispatch(formActions.resetForm(formName, response, false))
        }
      }
    })
  } else {
    dispatch(formActions.resetForm(formName, defaultProps, true))
  }
}

actions.invalidateList = (storeName) => (dispatch) => {
  dispatch(listActions.invalidateData(storeName))
}
