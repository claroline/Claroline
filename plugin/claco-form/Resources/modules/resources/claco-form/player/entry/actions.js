import cloneDeep from 'lodash/cloneDeep'

import {url} from '#/main/app/api'
import {makeActionCreator} from '#/main/core/scaffolding/actions'
import {API_REQUEST} from '#/main/app/api'
import {actions as formActions} from '#/main/core/data/form/actions'
import {actions as listActions} from '#/main/core/data/list/actions'

const ENTRIES_UPDATE = 'ENTRIES_UPDATE'
const ENTRY_CREATED = 'ENTRY_CREATED'
const CURRENT_ENTRY_LOAD = 'CURRENT_ENTRY_LOAD'
const ENTRY_COMMENT_ADD = 'ENTRY_COMMENT_ADD'
const ENTRY_COMMENT_UPDATE = 'ENTRY_COMMENT_UPDATE'
const ENTRY_COMMENT_REMOVE = 'ENTRY_COMMENT_REMOVE'
const ENTRY_USER_UPDATE = 'ENTRY_USER_UPDATE'
const ENTRY_USER_UPDATE_PROP = 'ENTRY_USER_UPDATE_PROP'
const ENTRY_USER_RESET = 'ENTRY_USER_RESET'
const ENTRY_CATEGORY_ADD = 'ENTRY_CATEGORY_ADD'
const ENTRY_CATEGORY_REMOVE = 'ENTRY_CATEGORY_REMOVE'
const ENTRY_KEYWORD_ADD = 'ENTRY_KEYWORD_ADD'
const ENTRY_KEYWORD_REMOVE = 'ENTRY_KEYWORD_REMOVE'
const USED_COUNTRIES_LOAD = 'USED_COUNTRIES_LOAD'

const actions = {}

actions.updateEntries = makeActionCreator(ENTRIES_UPDATE, 'entries')
actions.addCreatedEntry = makeActionCreator(ENTRY_CREATED, 'entry')
actions.loadCurrentEntry = makeActionCreator(CURRENT_ENTRY_LOAD, 'entry')
actions.addEntryComment = makeActionCreator(ENTRY_COMMENT_ADD, 'comment')
actions.updateEntryComment = makeActionCreator(ENTRY_COMMENT_UPDATE, 'comment')
actions.removeEntryComment = makeActionCreator(ENTRY_COMMENT_REMOVE, 'commentId')
actions.updateEntryUser = makeActionCreator(ENTRY_USER_UPDATE, 'entryUser')
actions.resetEntryUser = makeActionCreator(ENTRY_USER_RESET)
actions.addCategory = makeActionCreator(ENTRY_CATEGORY_ADD, 'category')
actions.removeCategory = makeActionCreator(ENTRY_CATEGORY_REMOVE, 'categoryId')
actions.addKeyword = makeActionCreator(ENTRY_KEYWORD_ADD, 'keyword')
actions.removeKeyword = makeActionCreator(ENTRY_KEYWORD_REMOVE, 'keywordId')
actions.loadUsedCountries = makeActionCreator(USED_COUNTRIES_LOAD, 'countries')

actions.deleteEntries = (entries) => (dispatch) => {
  dispatch({
    [API_REQUEST]: {
      url: url(['claro_claco_form_entries_delete', {ids: entries.map(e => e.id)}]),
      request: {
        method: 'PATCH'
      },
      success: (data, dispatch) => {
        dispatch(listActions.deleteItems('entries.list', entries))
      }
    }
  })
}

actions.switchEntryStatus = (entryId) => ({
  [API_REQUEST]: {
    url: ['claro_claco_form_entry_status_change', {entry: entryId}],
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => dispatch(actions.loadCurrentEntry(data))
  }
})

actions.switchEntriesStatus = (entries, status) => ({
  [API_REQUEST]: {
    url: url(['claro_claco_form_entries_status_change', {status: status, ids: entries.map(e => e.id)}]),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => dispatch(actions.updateEntries(data))
  }
})

actions.switchEntryLock = (entryId) => ({
  [API_REQUEST]: {
    url: ['claro_claco_form_entry_lock_switch', {entry: entryId}],
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => dispatch(actions.loadCurrentEntry(data))
  }
})

actions.switchEntriesLock = (entries, locked) => ({
  [API_REQUEST]: {
    url: url(['claro_claco_form_entries_lock_switch', {locked: locked ? 1 : 0, ids: entries.map(e => e.id)}]),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => dispatch(actions.updateEntries(data))
  }
})

actions.downloadEntryPdf = (entryId) => () => {
  window.location.href = url(['claro_claco_form_entry_pdf_download', {entry: entryId}])
}

actions.downloadEntriesPdf = (entries) => () => {
  window.location.href = url(['claro_claco_form_entries_pdf_download', {ids: entries.map(e => e.id)}])
}

actions.createComment = (entryId, content) => (dispatch) => {
  const formData = new FormData()
  formData.append('commentData', content)

  dispatch({
    [API_REQUEST]: {
      url: ['claro_claco_form_entry_comment_create', {entry: entryId}],
      request: {
        method: 'POST',
        body: formData
      },
      success: (data, dispatch) => {
        dispatch(actions.addEntryComment(data))
      }
    }
  })
}

actions.editComment = (commentId, content) => (dispatch) => {
  const formData = new FormData()
  formData.append('commentData', content)

  dispatch({
    [API_REQUEST]: {
      url: ['claro_claco_form_entry_comment_edit', {comment: commentId}],
      request: {
        method: 'POST',
        body: formData
      },
      success: (data, dispatch) => {
        dispatch(actions.updateEntryComment(data))
      }
    }
  })
}

actions.deleteComment = (commentId) => ({
  [API_REQUEST]: {
    url: ['claro_claco_form_entry_comment_delete', {comment: commentId}],
    request: {
      method: 'DELETE'
    },
    success: (data, dispatch) => {
      dispatch(actions.removeEntryComment(commentId))
    }
  }
})

actions.activateComment = (commentId) => ({
  [API_REQUEST]: {
    url: ['claro_claco_form_entry_comment_activate', {comment: commentId}],
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => {
      dispatch(actions.updateEntryComment(data))
    }
  }
})

actions.blockComment = (commentId) => ({
  [API_REQUEST]: {
    url: ['claro_claco_form_entry_comment_block', {comment: commentId}],
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => {
      dispatch(actions.updateEntryComment(data))
    }
  }
})

actions.changeEntryOwner = (entryId, userId) => ({
  [API_REQUEST]: {
    url: ['claro_claco_form_entry_user_change', {entry: entryId, user: userId}],
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => dispatch(actions.loadCurrentEntry(data))
  }
})

actions.shareEntry = (entryId, userId) => (dispatch) => {
  dispatch({
    [API_REQUEST]: {
      url: ['claro_claco_form_entry_user_share', {entry: entryId, user: userId}],
      request: {
        method: 'PUT'
      }
    }
  })
}

actions.unshareEntry = (entryId, userId) => (dispatch) => {
  dispatch({
    [API_REQUEST]: {
      url: ['claro_claco_form_entry_user_unshare', {entry: entryId, user: userId}],
      request: {
        method: 'PUT'
      }
    }
  })
}

actions.openForm = (formName, id = null, defaultProps) => {
  if (id) {
    return {
      [API_REQUEST]: {
        url: ['apiv2_clacoformentry_get', {id}],
        success: (data, dispatch) => dispatch(formActions.resetForm(formName, data, false))
      }
    }
  } else {
    return formActions.resetForm(formName, defaultProps, true)
  }
}

actions.loadEntryUser = (entryId) => ({
  [API_REQUEST]: {
    url: ['claro_claco_form_entry_user_retrieve', {entry: entryId}],
    success: (data, dispatch) => dispatch(actions.updateEntryUser(data))
  }
})

actions.updateEntryUserProp = makeActionCreator(ENTRY_USER_UPDATE_PROP, 'property', 'value')

actions.saveEntryUser = (entryUser) => ({
  [API_REQUEST]: {
    url: ['apiv2_clacoformentryuser_update', {id: entryUser['id']}],
    request: {
      method: 'PUT',
      body: JSON.stringify(entryUser)
    },
    success: (data, dispatch) => {
      dispatch(actions.updateEntryUser(data))
    }
  }
})

actions.editAndSaveEntryUser = (property, value) => (dispatch, getState) => {
  const entryUser = cloneDeep(getState().entries.entryUser)
  entryUser[property] = value
  dispatch(actions.saveEntryUser(entryUser))
}

actions.loadAllUsedCountries = (clacoFormId) => ({
  [API_REQUEST]: {
    url: ['claro_claco_form_used_countries_load', {clacoForm: clacoFormId}],
    request: {
      method: 'GET'
    },
    success: (data, dispatch) => {
      dispatch(actions.loadUsedCountries(data))
    }
  }
})

export {
  actions,
  ENTRIES_UPDATE,
  ENTRY_CREATED,
  CURRENT_ENTRY_LOAD,
  ENTRY_COMMENT_ADD,
  ENTRY_COMMENT_UPDATE,
  ENTRY_COMMENT_REMOVE,
  ENTRY_USER_UPDATE,
  ENTRY_USER_UPDATE_PROP,
  ENTRY_USER_RESET,
  ENTRY_CATEGORY_ADD,
  ENTRY_CATEGORY_REMOVE,
  ENTRY_KEYWORD_ADD,
  ENTRY_KEYWORD_REMOVE,
  USED_COUNTRIES_LOAD
}