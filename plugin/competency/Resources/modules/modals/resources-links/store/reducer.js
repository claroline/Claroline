import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {
  COMPETENCIES_LOAD,
  COMPETENCY_ADD,
  COMPETENCY_REMOVE,
  ABILITIES_LOAD,
  ABILITY_ADD,
  ABILITY_REMOVE
} from '#/plugin/competency/modals/resources-links/store/actions'

const reducer = combineReducers({
  competencies: makeReducer([], {
    [COMPETENCIES_LOAD]: (state, action) => action.competencies,
    [COMPETENCY_ADD]: (state, action) => {
      const newState = state.slice(0)

      newState.push(action.competency)

      return newState
    },
    [COMPETENCY_REMOVE]: (state, action) => {
      const newState = state.slice(0)

      const index = newState.findIndex(competency => competency.id === action.competency.id)

      if (-1 !== index) {
        newState.splice(index, 1)
      }

      return newState
    }
  }),
  abilities: makeReducer([], {
    [ABILITIES_LOAD]: (state, action) => action.abilities,
    [ABILITY_ADD]: (state, action) => {
      const newState = state.slice(0)

      newState.push(action.ability)

      return newState
    },
    [ABILITY_REMOVE]: (state, action) => {
      const newState = state.slice(0)

      const index = newState.findIndex(ability => ability.id === action.ability.id)

      if (-1 !== index) {
        newState.splice(index, 1)
      }

      return newState
    }
  })
})

export {
  reducer
}