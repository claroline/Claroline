import merge from 'lodash/merge'

import {now} from '#/main/app/intl/date'
import {currentUser} from '#/main/app/security'
import {makeId} from '#/main/core/scaffolding/id'
import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST, url} from '#/main/app/api'
import {actions as listActions} from '#/main/app/content/list/store'
import {actions as formActions} from '#/main/app/content/form/store'

import {selectors} from '#/plugin/message/tools/messaging/store/selectors'
import {Message as MessageTypes} from '#/plugin/message/prop-types'

// actions
export const MESSAGE_LOAD = 'MESSAGE_LOAD'
export const IS_REPLY = 'IS_REPLY'

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

actions.newMessage = (id = null) => {
  if (id) {
    return ({
      [API_REQUEST]: {
        url: ['apiv2_message_root', {id}],
        success: (data, dispatch) => {
          dispatch(formActions.resetForm(
            `${selectors.STORE_NAME}.messageForm`,
            merge({}, MessageTypes.defaultProps, {
              id: makeId(),
              from: currentUser(),
              to: data.from.username,
              object: `Re: ${data.object}`,
              meta: {date : now()}
            }),
            true
          ))
        }
      }
    })
  }

  return formActions.resetForm(
    `${selectors.STORE_NAME}.messageForm`,
    merge({}, MessageTypes.defaultProps, {
      id: makeId(),
      from: currentUser(),
      meta: {date : now()}
    }),
    true
  )
}

actions.deleteMessages = (messages) => ({
  [API_REQUEST]: {
    url: ['apiv2_message_user_remove', {ids: messages.map(message => message.id)}],
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

actions.setAsReply = makeActionCreator(IS_REPLY)
actions.loadMessage = makeActionCreator(MESSAGE_LOAD, 'message')

actions.fetchMessage = (id) => ({
  [API_REQUEST]: {
    url: ['apiv2_message_root', {id}],
    success: (data, dispatch) => {
      dispatch(actions.loadMessage(data))
    }
  }
})

actions.markedAsReadWhenOpen = (id) => ({
  [API_REQUEST]: {
    url: ['apiv2_message_read', {ids: [id]}],
    request: {
      method: 'PUT'
    }
  }
})

actions.openMessage = (id) => (dispatch) => {
  dispatch(actions.fetchMessage(id)).then((data) => dispatch(actions.markedAsReadWhenOpen(data.meta.umuuid)))
}

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
