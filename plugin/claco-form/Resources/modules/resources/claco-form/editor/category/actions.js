import {generateUrl} from '#/main/core/fos-js-router'
import {makeActionCreator} from '#/main/core/utilities/redux'
import {REQUEST_SEND} from '#/main/core/api/actions'

export const CATEGORY_ADD = 'CATEGORY_ADD'
export const CATEGORY_UPDATE = 'CATEGORY_UPDATE'
export const CATEGORY_REMOVE = 'CATEGORY_REMOVE'

export const actions = {}

actions.addCategory = makeActionCreator(CATEGORY_ADD, 'category')
actions.updateCategory = makeActionCreator(CATEGORY_UPDATE, 'category')
actions.removeCategory = makeActionCreator(CATEGORY_REMOVE, 'categoryId')

actions.createCategory = (category) => (dispatch, getState) => {
  const resourceId = getState().resource.id
  const managersIds = []
  category.managers.forEach(m => managersIds.push(m.id))
  category.managers = managersIds
  const formData = new FormData()
  formData.append('categoryData', JSON.stringify(category))

  dispatch({
    [REQUEST_SEND]: {
      url: generateUrl('claro_claco_form_category_create', {clacoForm: resourceId}),
      request: {
        method: 'POST',
        body: formData
      },
      success: (data, dispatch) => {
        dispatch(actions.addCategory(JSON.parse(data)))
      }
    }
  })
}

actions.editCategory = (category) => (dispatch) => {
  const managersIds = []
  category.managers.forEach(m => managersIds.push(m.id))
  category.managers = managersIds
  const formData = new FormData()
  formData.append('categoryData', JSON.stringify(category))

  dispatch({
    [REQUEST_SEND]: {
      url: generateUrl('claro_claco_form_category_edit', {category: category.id}),
      request: {
        method: 'POST',
        body: formData
      },
      success: (data, dispatch) => {
        dispatch(actions.updateCategory(JSON.parse(data)))
      }
    }
  })
}

actions.deleteCategory = (categoryId) => (dispatch) => {
  dispatch({
    [REQUEST_SEND]: {
      url: generateUrl('claro_claco_form_category_delete', {category: categoryId}),
      request: {
        method: 'DELETE'
      },
      success: (data, dispatch) => {
        dispatch(actions.removeCategory(categoryId))
      }
    }
  })
}
