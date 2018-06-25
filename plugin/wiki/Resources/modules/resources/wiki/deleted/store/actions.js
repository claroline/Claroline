import {makeActionCreator} from '#/main/core/scaffolding/actions'
import {API_REQUEST} from '#/main/app/api'

export const SECTION_PERMANENTLY_REMOVED = 'SECTION_PERMANENTLY_REMOVED'
export const SECTION_RESTORED = 'SECTION_RESTORED'

export const actions = {}

actions.sectionPermanentlyRemoved = makeActionCreator(SECTION_PERMANENTLY_REMOVED)
actions.sectionRestored = makeActionCreator(SECTION_RESTORED)

actions.removeSections = (wikiId = null, ids = []) => dispatch => {
  if (wikiId && ids && Array.isArray(ids) && ids.length > 0) {
    dispatch({
      [API_REQUEST]: {
        url: ['apiv2_wiki_section_delete', {wikiId}],
        request: {
          method: 'DELETE',
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            ids,
            permanently: true,
            children: false
          })
        },
        success: (data, dispatch) => {
          dispatch(actions.sectionPermanentlyRemoved(ids))
        }
      }
    })
  }
}

actions.restoreSections = (wikiId = null, ids = []) => dispatch => {
  if (wikiId && ids && Array.isArray(ids) && ids.length > 0) {
    dispatch({
      [API_REQUEST]: {
        url: ['apiv2_wiki_section_restore', {wikiId}],
        request: {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            ids
          })
        },
        success: (data, dispatch) => {
          dispatch(actions.sectionRestored(ids))
        }
      }
    })
  }
}