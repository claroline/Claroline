import {makeActionCreator} from '#/main/app/store/actions'

import {API_REQUEST, url} from '#/main/app/api'

export const COMPETENCIES_LOAD = 'COMPETENCIES_LOAD'
export const COMPETENCY_ADD = 'COMPETENCY_ADD'
export const COMPETENCY_REMOVE = 'COMPETENCY_REMOVE'
export const ABILITIES_LOAD = 'ABILITIES_LOAD'
export const ABILITY_ADD = 'ABILITY_ADD'
export const ABILITY_REMOVE = 'ABILITY_REMOVE'

export const actions = {}

actions.loadCompetencies = makeActionCreator(COMPETENCIES_LOAD, 'competencies')
actions.addCompetency = makeActionCreator(COMPETENCY_ADD, 'competency')
actions.removeCompetency = makeActionCreator(COMPETENCY_REMOVE, 'competency')
actions.loadAbilities = makeActionCreator(ABILITIES_LOAD, 'abilities')
actions.addAbility = makeActionCreator(ABILITY_ADD, 'ability')
actions.removeAbility = makeActionCreator(ABILITY_REMOVE, 'ability')

actions.fetchCompentencies = (nodeId) => ({
  [API_REQUEST]: {
    url: url(['apiv2_competency_resource_competencies_list', {node: nodeId}]),
    success: (response, dispatch) => dispatch(actions.loadCompetencies(response))
  }
})

actions.associateCompetency = (nodeId, competency) => ({
  [API_REQUEST]: {
    url: ['apiv2_competency_resource_associate', {node: nodeId, competency: competency.id}],
    request: {
      method: 'POST'
    },
    success: (response, dispatch) => dispatch(actions.addCompetency(response))
  }
})

actions.dissociateCompetency = (nodeId, competency) => ({
  [API_REQUEST]: {
    url: ['apiv2_competency_resource_dissociate', {node: nodeId, competency: competency.id}],
    request: {
      method: 'DELETE'
    },
    success: (response, dispatch) => dispatch(actions.removeCompetency(competency))
  }
})

actions.fetchAbilities = (nodeId) => ({
  [API_REQUEST]: {
    url: url(['apiv2_competency_resource_abilities_list', {node: nodeId}]),
    success: (response, dispatch) => dispatch(actions.loadAbilities(response))
  }
})

actions.associateAbility = (nodeId, ability) => ({
  [API_REQUEST]: {
    url: ['apiv2_competency_resource_ability_associate', {node: nodeId, ability: ability.id}],
    request: {
      method: 'POST'
    },
    success: (response, dispatch) => dispatch(actions.addAbility(response))
  }
})

actions.dissociateAbility = (nodeId, ability) => ({
  [API_REQUEST]: {
    url: ['apiv2_competency_resource_ability_dissociate', {node: nodeId, ability: ability.id}],
    request: {
      method: 'DELETE'
    },
    success: (response, dispatch) => dispatch(actions.removeAbility(ability))
  }
})