import get from 'lodash/get'

import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeFormReducer, FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store'
import {makeListReducer} from '#/main/app/content/list/store'

import {selectors as cursusSelectors} from '#/plugin/cursus/tools/cursus/store/selectors'
import {selectors} from '#/plugin/cursus/tools/cursus/catalog/store/selectors'
import {
  LOAD_COURSE,
  LOAD_COURSE_SESSION,
  LOAD_SESSION_USER,
  LOAD_SESSION_QUEUE,
  LOAD_SESSION_FULL,
  LOAD_EVENTS_REGISTRATION
} from '#/plugin/cursus/tools/cursus/catalog/store/actions'

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

  // old
  sessionUser: makeReducer(null, {
    [LOAD_SESSION_USER]: (state, action) => action.sessionUser
  }),
  sessionQueue: makeReducer(null, {
    [LOAD_SESSION_QUEUE]: (state, action) => action.sessionQueue
  }),
  isFull: makeReducer(false, {
    [LOAD_SESSION_FULL]: (state, action) => action.isFull
  }),
  eventsRegistration: makeReducer({}, {
    [LOAD_EVENTS_REGISTRATION]: (state, action) => action.eventsRegistration
  }),
  events: makeListReducer(cursusSelectors.STORE_NAME + '.catalog.events')
})

export {
  reducer
}