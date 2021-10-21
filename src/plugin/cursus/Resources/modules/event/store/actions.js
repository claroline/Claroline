import isEmpty from 'lodash/isEmpty'

import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST, url} from '#/main/app/api'
import {constants as actionConstants} from '#/main/app/action/constants'
import {actions as listActions} from '#/main/app/content/list/store/actions'
import {selectors} from '#/plugin/cursus/event/store/selectors'

export const LOAD_EVENT = 'LOAD_EVENT'
export const EVENT_SET_LOADED = 'EVENT_SET_LOADED'

export const actions = {}

actions.setLoaded = makeActionCreator(EVENT_SET_LOADED, 'loaded')
actions.loadEvent = makeActionCreator(LOAD_EVENT, 'event', 'registration')

actions.open = (id, force = false) => (dispatch, getState) => {
  const currentEvent = selectors.event(getState())
  if (force || isEmpty(currentEvent) || currentEvent.id !== id) {
    return dispatch({
      [API_REQUEST]: {
        url: ['apiv2_cursus_event_open', {id: id}],
        silent: true,
        before: () => {
          dispatch(actions.setLoaded(false))
          dispatch(actions.loadEvent(null, null))
        },
        success: (data) => {
          dispatch(actions.loadEvent(data.event, data.registration))
          dispatch(actions.setLoaded(true))
        }
      }
    })
  }
}

actions.register = (id) => ({
  [API_REQUEST]: {
    url: ['apiv2_cursus_event_self_register', {id: id}],
    request: {
      method: 'PUT'
    },
    success: (response, dispatch) => dispatch(actions.open(id, true))
  }
})

actions.addUsers = (eventId, users, type) => ({
  [API_REQUEST]: {
    url: url(['apiv2_cursus_event_add_users', {id: eventId, type: type}], {ids: users.map(user => user.id)}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      // TODO : do something better (I need it to recompute event available space)
      dispatch(actions.open(eventId, true))
    }
  }
})

actions.inviteUsers = (eventId, users) => ({
  [API_REQUEST]: {
    type: actionConstants.ACTION_SEND,
    url: url(['apiv2_cursus_event_invite_users', {id: eventId}], {ids: users.map(user => user.id)}),
    request: {
      method: 'PUT'
    }
  }
})

actions.addGroups = (eventId, groups, type) => ({
  [API_REQUEST]: {
    url: url(['apiv2_cursus_event_add_groups', {id: eventId, type: type}], {ids: groups.map(group => group.id)}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      // TODO : do something better (I need it to recompute event available space)
      dispatch(actions.open(eventId, true))
    }
  }
})

actions.inviteGroups = (eventId, groups) => ({
  [API_REQUEST]: {
    type: actionConstants.ACTION_SEND,
    url: url(['apiv2_cursus_event_invite_groups', {id: eventId}], {ids: groups.map(group => group.id)}),
    request: {
      method: 'PUT'
    }
  }
})

actions.setPresenceStatus = (eventId, presences, status) => ({
  [API_REQUEST]: {
    url: url(['apiv2_cursus_event_presence_update', {id: eventId, status: status}], {ids: presences.map(presence => presence.id)}),
    request: {
      method: 'PUT'
    },
    success: (response, dispatch) => dispatch(listActions.invalidateData(selectors.STORE_NAME+'.presences'))
  }
})