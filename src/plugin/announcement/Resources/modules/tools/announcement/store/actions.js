import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

export const ANNOUNCE_ADD    = 'ANNOUNCE_ADD'
export const ANNOUNCE_CHANGE = 'ANNOUNCE_CHANGE'
export const ANNOUNCE_DELETE = 'ANNOUNCE_DELETE'

export const ANNOUNCE_DETAIL_OPEN = 'ANNOUNCE_DETAIL_OPEN'
export const ANNOUNCE_DETAIL_RESET = 'ANNOUNCE_DETAIL_RESET'
export const ANNOUNCE_UPDATE_VIEWS = 'ANNOUNCE_UPDATE_VIEWS'

export const actions = {}

actions.openDetail = makeActionCreator(ANNOUNCE_DETAIL_OPEN, 'announceId')
actions.resetDetail = makeActionCreator(ANNOUNCE_DETAIL_RESET)

actions.addAnnounce = makeActionCreator(ANNOUNCE_ADD, 'announce')
actions.changeAnnounce = makeActionCreator(ANNOUNCE_CHANGE, 'announce')

actions.deleteAnnounce = makeActionCreator(ANNOUNCE_DELETE, 'announce')
actions.updateViews = makeActionCreator(ANNOUNCE_UPDATE_VIEWS, 'announceId', 'nbViews')

actions.removeAnnounce = (announce) => ({
  [API_REQUEST]: {
    url: ['claro_announcement_delete', {id: announce.id}],
    request: {
      method: 'DELETE'
    },
    success: (data, dispatch) => {
      dispatch(actions.deleteAnnounce(announce))
    }
  }
})

actions.exportPDF = (announce) => ({
  [API_REQUEST]: {
    url: ['claro_announcement_export_pdf', {id: announce.id}],
    request: {
      method: 'GET'
    }
  }
})

actions.updateView = (announceId) => (dispatch) => dispatch({
  [API_REQUEST]: {
    silent: true,
    url: ['claro_announcement_view_update', {id: announceId}],
    request: {
      method: 'PUT'
    },
    success: (response) => dispatch(actions.updateViews(announceId, response.nbViews))
  }
})