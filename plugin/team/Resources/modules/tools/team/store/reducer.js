import cloneDeep from 'lodash/cloneDeep'

import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {FORM_SUBMIT_SUCCESS, FORM_RESET} from '#/main/app/content/form/store/actions'

import {MY_TEAMS_ADD, MY_TEAMS_REMOVE} from '#/plugin/team/tools/team/store/actions'

const reducer = {
  teamParams: makeReducer({}, {
    // replaces team params data after success updates
    [FORM_SUBMIT_SUCCESS+'/teamParamsForm']: (state, action) => action.updatedData
  }),
  teamParamsForm: makeFormReducer('teamParamsForm'),
  teams: combineReducers({
    list: makeListReducer('teams.list', {}, {
      invalidated: makeReducer(false, {
        [FORM_SUBMIT_SUCCESS+'/teams.current']: () => true,
        [FORM_SUBMIT_SUCCESS+'/teams.multiple']: () => true
      })
    }),
    current: makeFormReducer('teams.current', {}, {
      users: makeListReducer('teams.current.users', {}, {
        invalidated: makeReducer(false, {
          [FORM_RESET+'/teams.current']: () => true
        })
      }),
      managers: makeListReducer('teams.current.managers', {}, {
        invalidated: makeReducer(false, {
          [FORM_RESET+'/teams.current']: () => true
        })
      }),
      usersPicker: makeListReducer('teams.current.usersPicker')
    }),
    multiple: makeFormReducer('teams.multiple')
  }),
  myTeams: makeReducer({}, {
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
  })
}

export {
  reducer
}