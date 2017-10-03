import {generateUrl} from '#/main/core/fos-js-router'
import {makeActionCreator} from '#/main/core/utilities/redux'
import {REQUEST_SEND} from '#/main/core/api/actions'

export const KEYWORD_ADD = 'KEYWORD_ADD'
export const KEYWORD_UPDATE = 'KEYWORD_UPDATE'
export const KEYWORD_REMOVE = 'KEYWORD_REMOVE'

export const actions = {}

actions.addKeyword = makeActionCreator(KEYWORD_ADD, 'keyword')
actions.updateKeyword = makeActionCreator(KEYWORD_UPDATE, 'keyword')
actions.removeKeyword = makeActionCreator(KEYWORD_REMOVE, 'keywordId')

actions.createKeyword = (keyword) => (dispatch, getState) => {
  const resourceId = getState().resource.id
  const formData = new FormData()
  formData.append('keywordData', JSON.stringify(keyword))

  dispatch({
    [REQUEST_SEND]: {
      url: generateUrl('claro_claco_form_keyword_create', {clacoForm: resourceId}),
      request: {
        method: 'POST',
        body: formData
      },
      success: (data, dispatch) => {
        dispatch(actions.addKeyword(JSON.parse(data)))
      }
    }
  })
}

actions.editKeyword = (keyword) => (dispatch) => {
  const formData = new FormData()
  formData.append('keywordData', JSON.stringify(keyword))

  dispatch({
    [REQUEST_SEND]: {
      url: generateUrl('claro_claco_form_keyword_edit', {keyword: keyword.id}),
      request: {
        method: 'POST',
        body: formData
      },
      success: (data, dispatch) => {
        dispatch(actions.updateKeyword(JSON.parse(data)))
      }
    }
  })
}

actions.deleteKeyword = (keywordId) => (dispatch) => {
  dispatch({
    [REQUEST_SEND]: {
      url: generateUrl('claro_claco_form_keyword_delete', {keyword: keywordId}),
      request: {
        method: 'DELETE'
      },
      success: (data, dispatch) => {
        dispatch(actions.removeKeyword(keywordId))
      }
    }
  })
}
