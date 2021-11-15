import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeFormReducer, FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store'
import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/plugin/cursus/tools/trainings/catalog/store/selectors'
import {
  LOAD_COURSE,
  LOAD_COURSE_SESSION
} from '#/plugin/cursus/tools/trainings/catalog/store/actions'

const reducer = combineReducers({
  courses: makeListReducer(selectors.LIST_NAME, {
    sortBy: {property: 'name', direction: 1}
  }, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS+'/'+selectors.FORM_NAME]: () => true
    })
  }),
  courseForm: makeFormReducer(selectors.FORM_NAME),
  course: makeReducer(null, {
    [LOAD_COURSE]: (state, action) => action.course
  }),
  courseDefaultSession: makeReducer(null, {
    [LOAD_COURSE]: (state, action) => action.defaultSession || null
  }),
  courseActiveSession: makeReducer(null, {
    [LOAD_COURSE_SESSION]: (state, action) => action.session
  }),
  courseAvailableSessions: makeReducer([], {
    [LOAD_COURSE]: (state, action) => action.availableSessions
  }),
  courseSessions: makeListReducer(selectors.STORE_NAME+'.courseSessions', {
    filters: [{property: 'status', value: 'not_ended'}],
    sortBy: {property: 'order', direction: 1}
  }, {
    invalidated: makeReducer(false, {
      [LOAD_COURSE]: () => true
    })
  }),
  courseEvents: makeListReducer(selectors.STORE_NAME+'.courseEvents', {
    filters: [{property: 'status', value: 'not_ended'}],
    sortBy: {property: 'startDate', direction: 1}
  }, {
    invalidated: makeReducer(false, {
      [LOAD_COURSE]: () => true,
      [LOAD_COURSE_SESSION]: () => true
    })
  }),

  coursePending: makeListReducer(selectors.STORE_NAME+'.coursePending', {}, {
    invalidated: makeReducer(false, {
      [LOAD_COURSE]: () => true
    })
  }),

  // current user registrations to course sessions
  courseRegistrations: makeReducer({users: [], groups: []}, {
    [LOAD_COURSE]: (state, action) => action.registrations
  }),

  // active session participants
  sessionTutors: makeListReducer(selectors.STORE_NAME+'.sessionTutors', {}, {
    invalidated: makeReducer(false, {
      [LOAD_COURSE]: () => true,
      [LOAD_COURSE_SESSION]: () => true
    })
  }),
  sessionUsers: makeListReducer(selectors.STORE_NAME+'.sessionUsers', {}, {
    invalidated: makeReducer(false, {
      [LOAD_COURSE]: () => true,
      [LOAD_COURSE_SESSION]: () => true
    })
  }),
  sessionGroups: makeListReducer(selectors.STORE_NAME+'.sessionGroups', {}, {
    invalidated: makeReducer(false, {
      [LOAD_COURSE]: () => true,
      [LOAD_COURSE_SESSION]: () => true
    })
  }),
  sessionPending: makeListReducer(selectors.STORE_NAME+'.sessionPending', {}, {
    invalidated: makeReducer(false, {
      [LOAD_COURSE]: () => true,
      [LOAD_COURSE_SESSION]: () => true
    })
  }),
  sessionCancellation: makeListReducer(selectors.STORE_NAME+'.sessionCancellation', {}, {
    invalidated: makeReducer(false, {
      [LOAD_COURSE]: () => true,
      [LOAD_COURSE_SESSION]: () => true,
      'LIST_DATA_INVALIDATE/trainingCatalog.sessionUsers': () => true
    })
  })
})

export {
  reducer
}