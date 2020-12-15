import {API_REQUEST} from '#/main/app/api'
import {actions as formActions} from '#/main/app/content/form/store'
import {actions as listActions} from '#/main/app/content/list/store'

import {selectors} from '#/main/core/administration/parameters/store/selectors'

export const actions = {}

actions.openIconSetForm = (formName, defaultProps, id = null) => (dispatch) => {
  if (id) {
    dispatch({
      [API_REQUEST]: {
        url: ['apiv2_icon_set_get', {id}],
        success: (response, dispatch) => {
          dispatch(formActions.resetForm(formName, response, false))
          dispatch(listActions.invalidateData(selectors.STORE_NAME+'.icons.items'))
        }
      }
    })
  } else {
    dispatch(formActions.resetForm(formName, defaultProps, true))
    dispatch(listActions.invalidateData(selectors.STORE_NAME+'.icons.items'))
  }
}

actions.resetForm = (formName) => (dispatch) => {
  dispatch(formActions.resetForm(formName, {}, true))
  dispatch(listActions.invalidateData(selectors.STORE_NAME+'.icons.items'))
}

actions.openIconItemForm = (formName, defaultProps, id = null) => (dispatch) => {
  if (id) {
    dispatch({
      [API_REQUEST]: {
        url: ['apiv2_icon_item_get', {id}],
        success: (response, dispatch) => {
          dispatch(formActions.resetForm(formName, response, false))
        }
      }
    })
  } else {
    dispatch(formActions.resetForm(formName, defaultProps, true))
  }
}
actions.updateIconItem = (iconSet, iconItem) => (dispatch) => {
  const formData = new FormData()
  formData.append('iconItem', JSON.stringify(iconItem))

  if (iconItem.file) {
    formData.append('file', iconItem.file)
  }

  dispatch({
    [API_REQUEST]: {
      url: ['apiv2_icon_set_item_update', {iconSet: iconSet.id}],
      type: 'upload',
      request: {
        method: 'POST',
        body: formData,
        headers: new Headers({
          //no Content type for automatic detection of boundaries.
          'X-Requested-With': 'XMLHttpRequest'
        })
      },
      success: (response, dispatch) => {
        dispatch(listActions.invalidateData(selectors.STORE_NAME+'.icons.items'))
      }
    }
  })
}
