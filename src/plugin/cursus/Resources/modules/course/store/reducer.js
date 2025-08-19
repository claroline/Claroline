import {makeListReducer} from '#/main/app/content/list/store'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {
  LOAD_COURSE,
  LOAD_COURSE_SESSION,
  LOAD_COURSE_STATS
} from '#/plugin/cursus/course/store/actions'
import {constants} from '#/plugin/cursus/constants'
import {selectors} from '#/plugin/cursus/course/store/selectors'

const reducer = combineReducers({
  course: makeReducer(null, {
    [LOAD_COURSE]: (state, action) => action.course
  }),
  defaultSession: makeReducer(null, {
    [LOAD_COURSE]: (state, action) => action.defaultSession || null
  }),
  activeSession: makeReducer(null, {
    [LOAD_COURSE_SESSION]: (state, action) => action.session
  }),
  availableSessions: makeReducer([], {
    [LOAD_COURSE]: (state, action) => action.availableSessions
  }),
  // current user registrations to course sessions
  registrations: makeReducer([], {
    [LOAD_COURSE]: (state, action) => action.registrations
  }),

  sessionUsers: makeListReducer(selectors.STORE_NAME+'.sessionUsers', {
    sortBy: {property: 'date', direction: -1},
    filters: {
      filters: [
        {property: 'type', value: constants.LEARNER_TYPE, locked: true, hidden: true}
      ]
    }
  }, {
    invalidated: makeReducer(false, {
      [LOAD_COURSE]: () => true,
      [LOAD_COURSE_SESSION]: () => true
    })
  }),

  stats: makeReducer(null, {
    [LOAD_COURSE_STATS]: (state, action) => action.stats
  })
})

export {
  reducer
}
