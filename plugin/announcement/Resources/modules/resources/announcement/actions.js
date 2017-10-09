import {makeActionCreator} from '#/main/core/utilities/redux'
import {generateUrl} from '#/main/core/fos-js-router'
import {navigate} from '#/main/core/router'

import {REQUEST_SEND} from '#/main/core/api/actions'

import {isValid} from './validator'

export const ANNOUNCE_ADD    = 'ANNOUNCE_ADD'
export const ANNOUNCE_CHANGE = 'ANNOUNCE_CHANGE'
export const ANNOUNCE_DELETE = 'ANNOUNCE_DELETE'

export const ANNOUNCES_SORT_TOGGLE  = 'ANNOUNCES_SORT_TOGGLE'
export const ANNOUNCES_PAGE_CHANGE = 'ANNOUNCES_PAGE_CHANGE'

export const ANNOUNCE_FORM_OPEN  = 'ANNOUNCE_FORM_OPEN'
export const ANNOUNCE_FORM_RESET = 'ANNOUNCE_FORM_RESET'
export const ANNOUNCE_FORM_VALIDATE  = 'ANNOUNCE_FORM_VALIDATE'
export const ANNOUNCE_FORM_UPDATE = 'ANNOUNCE_FORM_UPDATE'

export const ANNOUNCE_DETAIL_OPEN = 'ANNOUNCE_DETAIL_OPEN'
export const ANNOUNCE_DETAIL_RESET = 'ANNOUNCE_DETAIL_RESET'

export const actions = {}

actions.toggleAnnouncesSort = makeActionCreator(ANNOUNCES_SORT_TOGGLE)
actions.changeAnnouncesPage = makeActionCreator(ANNOUNCES_PAGE_CHANGE, 'page')

actions.openDetail = makeActionCreator(ANNOUNCE_DETAIL_OPEN, 'announceId')
actions.resetDetail = makeActionCreator(ANNOUNCE_DETAIL_RESET)

actions.openForm = makeActionCreator(ANNOUNCE_FORM_OPEN, 'announce')
actions.resetForm = makeActionCreator(ANNOUNCE_FORM_RESET)
actions.updateForm = makeActionCreator(ANNOUNCE_FORM_UPDATE, 'prop', 'value')
actions.validateForm = makeActionCreator(ANNOUNCE_FORM_VALIDATE)

actions.saveAnnounce = (aggregateId, announce) => {
  return (dispatch) => {
    dispatch(actions.validateForm())

    if (isValid(announce)) {
      if (announce.id) {
        dispatch(actions.updateAnnounce(aggregateId, announce))
      } else {
        dispatch(actions.createAnnounce(aggregateId, announce))
      }
    }
  }
}

actions.addAnnounce = makeActionCreator(ANNOUNCE_ADD, 'announce')
actions.createAnnounce = (aggregateId, announce) => ({
  [REQUEST_SEND]: {
    url: generateUrl('claro_announcement_create', {aggregateId: aggregateId}),
    request: {
      method: 'POST',
      body: JSON.stringify(announce)
    },
    success: (data, dispatch) => {
      dispatch(actions.addAnnounce(data))
      // open detail
      navigate('/'+data.id)
    }
  }
})

actions.changeAnnounce = makeActionCreator(ANNOUNCE_CHANGE, 'announce')
actions.updateAnnounce = (aggregateId, announce) => ({
  [REQUEST_SEND]: {
    url: generateUrl('claro_announcement_update', {aggregateId: aggregateId, id: announce.id}),
    request: {
      method: 'PUT',
      body: JSON.stringify(announce)
    },
    success: (data, dispatch) => {
      dispatch(actions.changeAnnounce(data))
      // open detail
      navigate('/'+announce.id)
    }
  }
})

actions.deleteAnnounce = makeActionCreator(ANNOUNCE_DELETE, 'announce')
actions.removeAnnounce = (aggregateId, announce) => ({
  [REQUEST_SEND]: {
    url: generateUrl('claro_announcement_delete', {aggregateId: aggregateId, id: announce.id}),
    request: {
      method: 'DELETE'
    },
    success: (data, dispatch) => {
      dispatch(actions.deleteAnnounce(announce))
      // open list
      navigate('/')
    }
  }
})

actions.sendAnnounce = (aggregateId, announce) => ({
  [REQUEST_SEND]: {
    url: generateUrl('claro_announcement_send', {aggregateId: aggregateId, id: announce.id}),
    request: {
      method: 'POST'
    }
  }
})
