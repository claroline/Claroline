import cloneDeep from 'lodash/cloneDeep'

import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeInstanceAction} from '#/main/app/store/actions'

import {TOOL_LOAD} from '#/main/core/tool/store/actions'

import {selectors as baseSelectors} from '#/main/community/tools/community/store/selectors'
import {selectors} from '#/main/community/tools/community/team/store/selectors'
import {MY_TEAMS_ADD, MY_TEAMS_REMOVE} from '#/main/community/tools/community/team/store/actions'

const reducer = combineReducers({
  // the list of the current user teams
  userTeams: makeReducer([], {
    [makeInstanceAction(TOOL_LOAD, baseSelectors.STORE_NAME)]: (state, action) => action.toolData.userTeams,
    [MY_TEAMS_ADD]: (state, action) => {
      const newState = cloneDeep(state)
      newState.push(action.team)

      return newState
    },
    [MY_TEAMS_REMOVE]: (state, action) => {
      const newState = cloneDeep(state)
      const index = newState.findIndex(team => team.id === action.team.id)

      if (0 <= index) {
        newState.splice(index, 1)
      }

      return newState
    }
  }),
  list: makeListReducer(selectors.LIST_NAME, {
    sortBy: {property: 'name', direction: 1}
  }, {
    invalidated: makeReducer(false, {
      [makeInstanceAction(TOOL_LOAD, baseSelectors.STORE_NAME)]: () => true,
      [makeInstanceAction(FORM_SUBMIT_SUCCESS, selectors.FORM_NAME)]: () => true
    })
  }),
  current: makeFormReducer(selectors.FORM_NAME, {}, {
    users: makeListReducer(selectors.FORM_NAME + '.users', {
      sortBy: {property: 'lastName', direction: 1}
    }, {
      invalidated: makeReducer(false, {
        [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: () => true
      })
    }),
    managers: makeListReducer(selectors.FORM_NAME + '.managers', {
      sortBy: {property: 'lastName', direction: 1}
    }, {
      invalidated: makeReducer(false, {
        [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: () => true
      })
    })
  })
})

export {
  reducer
}
