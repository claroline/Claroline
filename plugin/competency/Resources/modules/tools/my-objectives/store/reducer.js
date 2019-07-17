import cloneDeep from 'lodash/cloneDeep'

import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {TOOL_LOAD} from '#/main/core/tool/store/actions'

import {selectors} from '#/plugin/competency/tools/my-objectives/store/selectors'
import {
  COMPETENCIES_DATA_UPDATE,
  COMPETENCY_DATA_RESET,
  COMPETENCY_DATA_LOAD,
  COMPETENCY_DATA_UPDATE
} from '#/plugin/competency/tools/my-objectives/store/actions'

const objectivesReducer =  makeReducer({}, {
  [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.objectives
})

const competenciesReducer = makeReducer({}, {
  [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.competencies,
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

const objectivesCompetenciesReducer = makeReducer({}, {
  [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.objectivesCompetencies
})

const competencyReducer = makeReducer({}, {
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


const reducer = combineReducers({
  objectives: objectivesReducer,
  competencies: competenciesReducer,
  objectivesCompetencies: objectivesCompetenciesReducer,
  competency: competencyReducer
})

export {
  reducer
}
