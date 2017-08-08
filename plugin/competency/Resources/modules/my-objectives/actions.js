import {makeActionCreator} from '#/main/core/utilities/redux'
import {generateUrl} from '#/main/core/fos-js-router'
import {REQUEST_SEND} from '#/main/core/api/actions'
import {trans} from '#/main/core/translation'

export const COMPETENCIES_DATA_UPDATE = 'COMPETENCIES_DATA_UPDATE'
export const COMPETENCY_DATA_RESET = 'COMPETENCY_DATA_RESET'
export const COMPETENCY_DATA_LOAD = 'COMPETENCY_DATA_LOAD'
export const COMPETENCY_DATA_UPDATE = 'COMPETENCY_DATA_UPDATE'

export const actions = {}

//actions.displayCompetencyView = (objectiveId, competencyId) => {
//  return (dispatch) => {
//    dispatch(actions.fetchCompetencyData(objectiveId, competencyId))
//    dispatch(actions.updateViewMode(VIEW_COMPETENCY))
//  }
//}

actions.updateCompetenciesData = makeActionCreator(COMPETENCIES_DATA_UPDATE, 'competencyId', 'property', 'value')

actions.resetCompetencyData = makeActionCreator(COMPETENCY_DATA_RESET)
actions.loadCompetencyData = makeActionCreator(COMPETENCY_DATA_LOAD, 'data')
actions.updateCompetencyData = makeActionCreator(COMPETENCY_DATA_UPDATE, 'data')

actions.fetchCompetencyData = (objectiveId, competencyId) => {
  return (dispatch) => {
    dispatch({
      [REQUEST_SEND]: {
        url: generateUrl('hevinci_my_objectives_competency', {objective: objectiveId, competency: competencyId}),
        request: {method: 'GET'},
        success: (data, dispatch) => {
          dispatch(actions.loadCompetencyData(data))
        }
      }
    })
  }
}

actions.fetchLevelData = (competencyId, level) => {
  return (dispatch) => {
    dispatch({
      [REQUEST_SEND]: {
        url: generateUrl('hevinci_my_objectives_competency_level', {competency: competencyId, level: level}),
        request: {method: 'GET'},
        success: (data, dispatch) => {
          dispatch(actions.updateCompetencyData(data))
        }
      }
    })
  }
}

actions.fetchRelevantResource = (competencyId, level) => {
  return (dispatch) => {
    dispatch({
      [REQUEST_SEND]: {
        url: generateUrl('hevinci_my_objectives_competency_resource_fetch', {competency: competencyId, level: level}),
        request: {method: 'GET'},
        success: (data) => {
          if (data && data['resourceId']) {
            const url = generateUrl('claro_resource_open_short', {node: data['resourceId']})
            window.location.href = url
          } else {
            dispatch(actions.updateCompetenciesData(competencyId, 'error', trans('objective.invalid_challenge_msg', {}, 'competency')))
          }
        }
      }
    })
  }
}