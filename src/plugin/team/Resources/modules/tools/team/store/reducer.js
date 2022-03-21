import cloneDeep from 'lodash/cloneDeep'

import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {FORM_SUBMIT_SUCCESS, FORM_RESET} from '#/main/app/content/form/store/actions'

import {TOOL_LOAD} from '#/main/core/tool/store/actions'

import {selectors} from '#/plugin/team/tools/team/store/selectors'
import {MY_TEAMS_ADD, MY_TEAMS_REMOVE} from '#/plugin/team/tools/team/store/actions'

const reducer = combineReducers({
  teamParams: makeReducer({}, {
    [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.teamParams,
    // replaces team params data after success updates
    [FORM_SUBMIT_SUCCESS + '/' + selectors.STORE_NAME + '.teamParamsForm']: (state, action) => action.updatedData
  }),
  teamParamsForm: makeFormReducer(selectors.STORE_NAME + '.teamParamsForm'),
  teams: combineReducers({
    list: makeListReducer(selectors.STORE_NAME + '.teams.list', {
      sortBy: {property: 'id', direction: -1}
    }, {
      invalidated: makeReducer(false, {
        [FORM_SUBMIT_SUCCESS + '/' + selectors.STORE_NAME + '.teams.current']: () => true,
        [FORM_SUBMIT_SUCCESS + '/' + selectors.STORE_NAME + '.teams.multiple']: () => true,
        [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: () => true
      })
    }),
    current: makeFormReducer(selectors.STORE_NAME + '.teams.current', {}, {
      users: makeListReducer(selectors.STORE_NAME + '.teams.current.users', {}, {
        invalidated: makeReducer(false, {
          [FORM_RESET + '/' + selectors.STORE_NAME + '.teams.current']: () => true,
          [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: () => true
        })
      }),
      managers: makeListReducer(selectors.STORE_NAME + '.teams.current.managers', {}, {
        invalidated: makeReducer(false, {
          [FORM_RESET + '/' + selectors.STORE_NAME + '.teams.current']: () => true,
          [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: () => true
        })
      })
    }),
    multiple: makeFormReducer(selectors.STORE_NAME + '.teams.multiple')
  }),
  myTeams: makeReducer({}, {
    [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.myTeams,
    [MY_TEAMS_ADD]: (state, action) => {
      const newState = cloneDeep(state)
      newState.push(action.teamId)

      return newState
    },
    [MY_TEAMS_REMOVE]: (state, action) => {
      const newState = cloneDeep(state)
      const index = newState.findIndex(id => id === action.teamId)

      if (0 <= index) {
        newState.splice(index, 1)
      }

      return newState
    }
  }),
  canEdit: makeReducer(false, {
    [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.canEdit || false
  }),
  resourceTypes: makeReducer([], {
    [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.resourceTypes || []
  })
})

export {
  reducer
}