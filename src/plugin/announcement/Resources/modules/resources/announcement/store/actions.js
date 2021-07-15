import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

export const ANNOUNCE_ADD    = 'ANNOUNCE_ADD'
export const ANNOUNCE_CHANGE = 'ANNOUNCE_CHANGE'
export const ANNOUNCE_DELETE = 'ANNOUNCE_DELETE'

export const ANNOUNCES_SORT_TOGGLE  = 'ANNOUNCES_SORT_TOGGLE'
export const ANNOUNCES_PAGE_CHANGE = 'ANNOUNCES_PAGE_CHANGE'

export const ANNOUNCE_DETAIL_OPEN = 'ANNOUNCE_DETAIL_OPEN'
export const ANNOUNCE_DETAIL_RESET = 'ANNOUNCE_DETAIL_RESET'

export const actions = {}

actions.toggleAnnouncesSort = makeActionCreator(ANNOUNCES_SORT_TOGGLE)
actions.changeAnnouncesPage = makeActionCreator(ANNOUNCES_PAGE_CHANGE, 'page')

actions.openDetail = makeActionCreator(ANNOUNCE_DETAIL_OPEN, 'announceId')
actions.resetDetail = makeActionCreator(ANNOUNCE_DETAIL_RESET)

actions.addAnnounce = makeActionCreator(ANNOUNCE_ADD, 'announce')
actions.changeAnnounce = makeActionCreator(ANNOUNCE_CHANGE, 'announce')

actions.deleteAnnounce = makeActionCreator(ANNOUNCE_DELETE, 'announce')
actions.removeAnnounce = (aggregateId, announce) => ({
  [API_REQUEST]: {
    url: ['claro_announcement_delete', {aggregateId: aggregateId, id: announce.id}],
    request: {
      method: 'DELETE'
    },
    success: (data, dispatch) => {
      dispatch(actions.deleteAnnounce(announce))
    }
  }
})
