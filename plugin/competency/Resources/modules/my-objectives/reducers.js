import cloneDeep from 'lodash/cloneDeep'
import {makeReducer} from '#/main/core/utilities/redux'
import {
  COMPETENCIES_DATA_UPDATE,
  COMPETENCY_DATA_RESET,
  COMPETENCY_DATA_LOAD,
  COMPETENCY_DATA_UPDATE
} from './actions'

const objectivesReducers =  makeReducer({}, {})

const competenciesReducers = makeReducer({}, {
  [COMPETENCIES_DATA_UPDATE]: (state, action) => {
    const copy = {}
    Object.keys(state).forEach(key => {
      copy[key] = {}
      Object.keys(state[key]).forEach(k => {
        if (parseInt(k) === parseInt(action.competencyId)) {
          const competencyCopy = cloneDeep(state[key][k])
          competencyCopy[action.property] = action.value
          copy[key][k] = competencyCopy
        } else {
          copy[key][k] = state[key][k]
        }
      })
    })

    return copy
  }
})

const competencyReducers = makeReducer({}, {
  [COMPETENCY_DATA_RESET]: () => {return {}},
  [COMPETENCY_DATA_LOAD]: (state, action) => {
    return {
      data: action.data.competency,
      objective: action.data.objective,
      progress: action.data.progress,
      nbLevels: action.data.nbLevels,
      currentLevel: action.data.currentLevel,
      challenge: action.data.challenge
    }
  },
  [COMPETENCY_DATA_UPDATE]: (state, action) => {
    return {
      data: state.data,
      objective: state.objective,
      progress: state.progress,
      nbLevels: state.nbLevels,
      currentLevel: action.data.currentLevel,
      challenge: action.data.challenge
    }
  }
})

export {
  objectivesReducers,
  competenciesReducers,
  competencyReducers
}
