import {generateUrl} from '#/main/core/api/router'
import {makeActionCreator} from '#/main/core/scaffolding/actions'
import {getDataQueryString} from '#/main/core/data/list/utils'
import {API_REQUEST} from '#/main/core/api/actions'

export const actions = {}

export const ENTRY_ADD = 'ENTRY_ADD'
export const ENTRY_UPDATE = 'ENTRY_UPDATE'
export const ENTRY_REMOVE = 'ENTRY_REMOVE'
export const CURRENT_ENTRY_LOAD = 'CURRENT_ENTRY_LOAD'
export const CURRENT_ENTRY_UPDATE = 'CURRENT_ENTRY_UPDATE'
export const ENTRY_COMMENT_ADD = 'ENTRY_COMMENT_ADD'
export const ENTRY_COMMENT_UPDATE = 'ENTRY_COMMENT_UPDATE'
export const ENTRY_COMMENT_REMOVE = 'ENTRY_COMMENT_REMOVE'
export const ALL_ENTRIES_REMOVE = 'ALL_ENTRIES_REMOVE'

actions.addEntry = makeActionCreator(ENTRY_ADD, 'entry')
actions.updateEntry = makeActionCreator(ENTRY_UPDATE, 'entry')
actions.removeEntry = makeActionCreator(ENTRY_REMOVE, 'entryId')
actions.loadCurrentEntry = makeActionCreator(CURRENT_ENTRY_LOAD, 'entry')
actions.updateCurrentEntry = makeActionCreator(CURRENT_ENTRY_UPDATE, 'property', 'value')
actions.addEntryComment = makeActionCreator(ENTRY_COMMENT_ADD, 'entryId', 'comment')
actions.updateEntryComment = makeActionCreator(ENTRY_COMMENT_UPDATE, 'entryId', 'comment')
actions.removeEntryComment = makeActionCreator(ENTRY_COMMENT_REMOVE, 'entryId', 'commentId')
actions.removeAllEntries = makeActionCreator(ALL_ENTRIES_REMOVE)

actions.createEntry = (entry, keywords, files) => (dispatch, getState) => {
  const resourceId = getState().resource.id
  const formData = new FormData()
  formData.append('entryData', JSON.stringify(entry))
  formData.append('keywordsData', JSON.stringify(keywords))

  Object.keys(files).forEach(fieldId => {
    files[fieldId].forEach((f, idx) => formData.append(`${fieldId}-${idx}`, f))
  })

  dispatch({
    [API_REQUEST]: {
      url: ['claro_claco_form_entry_create', {clacoForm: resourceId}],
      request: {
        method: 'POST',
        body: formData
      },
      success: (data, dispatch) => {
        dispatch(actions.addEntry(data))
        dispatch(actions.loadCurrentEntry(data))
      }
    }
  })
}

actions.editEntry = (entryId, entry, keywords, categories, files) => (dispatch) => {
  const formData = new FormData()
  formData.append('entryData', JSON.stringify(entry))
  formData.append('keywordsData', JSON.stringify(keywords))
  formData.append('categoriesData', JSON.stringify(categories))

  Object.keys(files).forEach(fieldId => {
    files[fieldId].forEach((f, idx) => formData.append(`${fieldId}-${idx}`, f))
  })

  dispatch({
    [API_REQUEST]: {
      url: ['claro_claco_form_entry_edit', {entry: entryId}],
      request: {
        method: 'POST',
        body: formData
      },
      success: (data, dispatch) => {
        dispatch(actions.updateEntry(data))
        dispatch(actions.loadCurrentEntry(data))
      }
    }
  })
}

actions.deleteEntry = (entryId) => (dispatch) => {
  dispatch({
    [API_REQUEST]: {
      url: ['claro_claco_form_entry_delete', {entry: entryId}],
      request: {
        method: 'DELETE'
      },
      success: (data, dispatch) => {
        dispatch(actions.removeEntry(entryId))
      }
    }
  })
}

actions.deleteEntries = (entries) => (dispatch) => {
  dispatch({
    [API_REQUEST]: {
      url: generateUrl('claro_claco_form_entries_delete') + getDataQueryString(entries),
      request: {
        method: 'PATCH'
      },
      success: (data, dispatch) => {
        data.forEach(e => dispatch(actions.removeEntry(e.id)))
      }
    }
  })
}

actions.loadEntry = (entryId) => (dispatch, getState) => {
  const state = getState()
  const currentEntry = state.currentEntry

  if (!currentEntry || currentEntry.id !== entryId) {
    const entries = state.entries.data
    let entry = entries.find(e => e.id === entryId)

    if (entry) {
      dispatch(actions.loadCurrentEntry(entry))
    } else {
      dispatch({
        [API_REQUEST]: {
          url: ['claro_claco_form_entry_retrieve', {entry: entryId}],
          request: {
            method: 'GET'
          },
          success: (data, dispatch) => {
            dispatch(actions.loadCurrentEntry(data))
          }
        }
      })
    }
  }
}

actions.switchEntryStatus = (entryId) => (dispatch, getState) => {
  const currentEntry = getState().currentEntry

  dispatch({
    [API_REQUEST]: {
      url: ['claro_claco_form_entry_status_change', {entry: entryId}],
      request: {
        method: 'PUT'
      },
      success: (data, dispatch) => {
        dispatch(actions.updateEntry(data))

        if (currentEntry && currentEntry.id === entryId) {
          dispatch(actions.loadCurrentEntry(data))
        }
      }
    }
  })
}

actions.switchEntriesStatus = (entries, status) => (dispatch, getState) => {
  const currentEntry = getState().currentEntry

  dispatch({
    [API_REQUEST]: {
      url: generateUrl('claro_claco_form_entries_status_change', {status: status}) + getDataQueryString(entries),
      request: {
        method: 'PATCH'
      },
      success: (data, dispatch) => {
        data.forEach(e => {
          dispatch(actions.updateEntry(e))

          if (currentEntry && currentEntry.id === e.id) {
            dispatch(actions.loadCurrentEntry(e))
          }
        })
      }
    }
  })
}

actions.switchEntryLock = (entryId) => (dispatch, getState) => {
  const currentEntry = getState().currentEntry

  dispatch({
    [API_REQUEST]: {
      url: ['claro_claco_form_entry_lock_switch', {entry: entryId}],
      request: {
        method: 'PUT'
      },
      success: (data, dispatch) => {
        dispatch(actions.updateEntry(data))

        if (currentEntry && currentEntry.id === entryId) {
          dispatch(actions.loadCurrentEntry(data))
        }
      }
    }
  })
}

actions.switchEntriesLock = (entries, locked) => (dispatch, getState) => {
  const currentEntry = getState().currentEntry

  dispatch({
    [API_REQUEST]: {
      url: generateUrl('claro_claco_form_entries_lock_switch', {locked: locked ? 1 : 0}) + getDataQueryString(entries),
      request: {
        method: 'PATCH'
      },
      success: (data, dispatch) => {
        data.forEach(e => {
          dispatch(actions.updateEntry(e))

          if (currentEntry && currentEntry.id === e.id) {
            dispatch(actions.loadCurrentEntry(e))
          }
        })
      }
    }
  })
}

actions.downloadEntryPdf = (entryId) => () => {
  window.location.href = generateUrl('claro_claco_form_entry_pdf_download', {entry: entryId})
}

actions.downloadEntriesPdf = (entries) => () => {
  window.location.href = generateUrl('claro_claco_form_entries_pdf_download') + getDataQueryString(entries)
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
        dispatch(actions.addEntryComment(entryId, data))
      }
    }
  })
}

actions.editComment = (entryId, commentId, content) => (dispatch) => {
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
        dispatch(actions.updateEntryComment(entryId, data))
      }
    }
  })
}

actions.deleteComment = (entryId, commentId) => (dispatch) => {
  dispatch({
    [API_REQUEST]: {
      url: ['claro_claco_form_entry_comment_delete', {comment: commentId}],
      request: {
        method: 'DELETE'
      },
      success: (data, dispatch) => {
        dispatch(actions.removeEntryComment(entryId, commentId))
      }
    }
  })
}

actions.activateComment = (entryId, commentId) => (dispatch) => {
  dispatch({
    [API_REQUEST]: {
      url: ['claro_claco_form_entry_comment_activate', {comment: commentId}],
      request: {
        method: 'PUT'
      },
      success: (data, dispatch) => {
        dispatch(actions.updateEntryComment(entryId, JSON.parse(data)))
      }
    }
  })
}

actions.blockComment = (entryId, commentId) => (dispatch) => {
  dispatch({
    [API_REQUEST]: {
      url: ['claro_claco_form_entry_comment_block', {comment: commentId}],
      request: {
        method: 'PUT'
      },
      success: (data, dispatch) => {
        dispatch(actions.updateEntryComment(entryId, JSON.parse(data)))
      }
    }
  })
}

actions.saveEntryUser = (entryId, entryUser) => (dispatch) => {
  const formData = new FormData()
  formData.append('entryUserData', JSON.stringify(entryUser))

  dispatch({
    [API_REQUEST]: {
      url: ['claro_claco_form_entry_user_save', {entry: entryId}],
      request: {
        method: 'POST',
        body: formData
      }
    }
  })
}

actions.changeEntryOwner = (entryId, userId) => (dispatch, getState) => {
  const currentEntry = getState().currentEntry

  dispatch({
    [API_REQUEST]: {
      url: ['claro_claco_form_entry_user_change', {entry: entryId, user: userId}],
      request: {
        method: 'PUT'
      },
      success: (data, dispatch) => {
        dispatch(actions.updateEntry(JSON.parse(data)))

        if (currentEntry && currentEntry.id === entryId) {
          dispatch(actions.loadCurrentEntry(JSON.parse(data)))
        }
      }
    }
  })
}

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