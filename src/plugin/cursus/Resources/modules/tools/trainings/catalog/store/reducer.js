import get from 'lodash/get'

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
  courseActiveSession: makeReducer(null, {
    [LOAD_COURSE]: (state, action) => action.defaultSession ? action.defaultSession : get(action, 'availableSessions[0]', null),
    [LOAD_COURSE_SESSION]: (state, action) => action.session
  }),
  courseAvailableSessions: makeReducer([], {
    [LOAD_COURSE]: (state, action) => action.availableSessions
  }),
  courseSessions: makeListReducer(selectors.STORE_NAME+'.courseSessions', {
    sortBy: {property: 'order', direction: 1}
  }, {
    invalidated: makeReducer(false, {
      [LOAD_COURSE]: () => true
    })
  }),
  courseEvents: makeListReducer(selectors.STORE_NAME+'.courseEvents', {
    sortBy: {property: 'startDate', direction: 1}
  }, {
    invalidated: makeReducer(false, {
      [LOAD_COURSE]: () => true,
      [LOAD_COURSE_SESSION]: () => true
    })
  }),

  // current user registrations to course sessions
  courseRegistrations: makeReducer({users: [], groups: []}, {
    [LOAD_COURSE]: (state, action) => action.registrations
  }),

  // participants
  courseTutors: makeListReducer(selectors.STORE_NAME+'.courseTutors', {}, {
    invalidated: makeReducer(false, {
      [LOAD_COURSE]: () => true,
      [LOAD_COURSE_SESSION]: () => true
    })
  }),
  courseUsers: makeListReducer(selectors.STORE_NAME+'.courseUsers', {}, {
    invalidated: makeReducer(false, {
      [LOAD_COURSE]: () => true,
      [LOAD_COURSE_SESSION]: () => true
    })
  }),
  courseGroups: makeListReducer(selectors.STORE_NAME+'.courseGroups', {}, {
    invalidated: makeReducer(false, {
      [LOAD_COURSE]: () => true,
      [LOAD_COURSE_SESSION]: () => true
    })
  }),
  coursePending: makeListReducer(selectors.STORE_NAME+'.coursePending', {}, {
    invalidated: makeReducer(false, {
      [LOAD_COURSE]: () => true,
      [LOAD_COURSE_SESSION]: () => true
    })
  })
})

export {
  reducer
}