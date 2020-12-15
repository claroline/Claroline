
import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST, url} from '#/main/app/api'
import {actions as listActions} from '#/main/app/content/list/store'

import {selectors} from '#/plugin/message/tools/messaging/store/selectors'

// actions
export const MESSAGE_LOAD = 'MESSAGE_LOAD'

// actions creators
export const actions = {}

actions.addContacts = users => ({
  [API_REQUEST]: {
    url: url(['apiv2_contacts_create'], {ids: users}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => dispatch(listActions.invalidateData(`${selectors.STORE_NAME}.contacts`))
  }
})

actions.loadMessage = makeActionCreator(MESSAGE_LOAD, 'message')
actions.openMessage = (id) => (dispatch) => dispatch({
  [API_REQUEST]: {
    silent: true,
    url: ['apiv2_message_root', {id}],
    success: (data) => {
      dispatch(actions.loadMessage(data))
      if (!data.meta.read) {
        dispatch(actions.markedAsReadWhenOpen(data.meta.umuuid))
      }
    }
  }
})

actions.sendMessage = (message) => dispatch => dispatch({
  [API_REQUEST]: {
    type: 'send',
    url: ['apiv2_message_create'],
    request: {
      method: 'POST',
      body: JSON.stringify(message)
    },
    success: (response) => dispatch(actions.openMessage(response.id))
  }
})

actions.deleteMessages = (messages) => ({
  [API_REQUEST]: {
    url: ['apiv2_message_hard_delete', {ids: messages.map(message => message.meta.umuuid)}],
    request: {
      method: 'DELETE'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(`${selectors.STORE_NAME}.deletedMessages`))
    }
  }
})

actions.removeMessages = (messages, listName) => ({
  [API_REQUEST]: {
    url: ['apiv2_message_soft_delete', {ids: messages.map(message => message.meta.umuuid)}],
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => dispatch(listActions.invalidateData(listName))
  }
})

actions.restoreMessages = (messages) => ({
  [API_REQUEST]: {
    url: ['apiv2_message_restore', {ids: messages.map(message => message.meta.umuuid)}],
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(`${selectors.STORE_NAME}.deletedMessages`))
    }
  }
})

actions.markedAsReadWhenOpen = (id) => ({
  [API_REQUEST]: {
    silent: true,
    url: ['apiv2_message_read', {ids: [id]}],
    request: {
      method: 'PUT'
    }
  }
})

actions.readMessages = (messages) => ({
  [API_REQUEST]: {
    url: ['apiv2_message_read', {ids: messages.map(message => message.meta.umuuid)}],
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(`${selectors.STORE_NAME}.receivedMessages`))
    }
  }
})

actions.unreadMessages = (messages) => ({
  [API_REQUEST]: {
    url: ['apiv2_message_unread', {ids: messages.map(message => message.meta.umuuid)}],
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(`${selectors.STORE_NAME}.receivedMessages`))
    }
  }
})
