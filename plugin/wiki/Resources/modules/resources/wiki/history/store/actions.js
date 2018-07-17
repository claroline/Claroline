import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'
import {findInTree} from '#/plugin/wiki/resources/wiki/utils'

export const UPDATE_CURRENT_HISTORY_SECTION = 'UPDATE_CURRENT_HISTORY_SECTION'
export const UPDATE_CURRENT_HISTORY_VERSION = 'UPDATE_CURRENT_HISTORY_VERSION'
export const UPDATE_CURRENT_HISTORY_COMPARE_SET = 'UPDATE_CURRENT_HISTORY_COMPARE_SET'
export const UPDATE_ACTIVE_CONTRIBUTION = 'UPDATE_ACTIVE_CONTRIBUTION'

export const actions = {}

actions.updateCurrentHistorySection = makeActionCreator(UPDATE_CURRENT_HISTORY_SECTION, 'section')
actions.updateCurrentHistoryVersion = makeActionCreator(UPDATE_CURRENT_HISTORY_VERSION, 'contribution')
actions.updateCurrentHistoryCompareSet = makeActionCreator(UPDATE_CURRENT_HISTORY_COMPARE_SET, 'contributions')
actions.updateActiveContribution = makeActionCreator(UPDATE_ACTIVE_CONTRIBUTION, 'sectionId', 'contribution')

actions.setCurrentHistorySection = (sectionId = null) => (dispatch, getState) => {
  if (sectionId !== null) {
    dispatch(actions.updateCurrentHistorySection(findInTree(getState().sections.tree, sectionId)))
  } else {
    dispatch(actions.updateCurrentHistorySection({}))
  }
}

actions.setCurrentHistoryVersion = (sectionId, id) => (dispatch, getState) => {
  dispatch(actions.setCurrentHistorySection(sectionId))
  if (id !== null) {
    const contribution = getState().history.contributions.data.find(item => item.id === id)
    if (contribution) {
      dispatch(actions.updateCurrentHistoryVersion(contribution))
    } else {
      dispatch({
        [API_REQUEST]: {
          url: ['apiv2_wiki_section_contribution_get', {sectionId, id}],
          request: {
            method: 'GET'
          },
          success: (data, dispatch) => {
            dispatch(actions.updateCurrentHistoryVersion(data))
          }
        }
      })
    }
  } else {
    dispatch(actions.updateCurrentHistoryVersion({}))
  }
}


actions.setCurrentHistoryCompareSet = (sectionId, id1, id2) => (dispatch) => {
  if (sectionId !== null && id1 !== null && id2 !== null) {
    dispatch({
      [API_REQUEST]: {
        url: ['apiv2_wiki_section_contribution_compare', {sectionId, id1, id2}],
        request: {
          method: 'GET'
        },
        success: (data, dispatch) => {
          dispatch(actions.setCurrentHistorySection(sectionId))
          dispatch(actions.updateCurrentHistoryCompareSet(data))
        }
      }
    })
  } else {
    dispatch(actions.updateCurrentHistorySection({}))
    dispatch(actions.updateCurrentHistoryCompareSet([]))
  }
}

actions.setActiveContribution = (sectionId = null, id = null) => (dispatch) => {
  if (sectionId !== null && id !== null) {
    dispatch({
      [API_REQUEST]: {
        url: ['apiv2_wiki_section_contribution_set_active', {sectionId, id}],
        request: {
          method: 'PUT'
        },
        success: (data, dispatch) => {
          dispatch(actions.updateActiveContribution(sectionId, data))
        }
      }
    })
  }
}