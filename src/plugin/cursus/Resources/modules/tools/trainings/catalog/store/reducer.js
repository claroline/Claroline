import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeFormReducer, FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store'
import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/plugin/cursus/tools/trainings/catalog/store/selectors'
import {
  LOAD_COURSE,
  LOAD_COURSE_SESSION,
  LOAD_COURSE_STATS,
  SWITCH_PARTICIPANTS_VIEW
} from '#/plugin/cursus/tools/trainings/catalog/store/actions'
import {constants} from '#/plugin/cursus/constants'

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
  // current user registrations to course sessions
  courseRegistrations: makeReducer({users: [], groups: []}, {
    [LOAD_COURSE]: (state, action) => action.registrations
  }),

  participantsView: makeReducer('session', {
    [SWITCH_PARTICIPANTS_VIEW]: (state, action) => action.viewMode
  }),

  coursePending: makeListReducer(selectors.STORE_NAME+'.coursePending', {}, {
    invalidated: makeReducer(false, {
      [LOAD_COURSE]: () => true
    })
  }),

  // active session participants
  sessionTutors: makeListReducer(selectors.STORE_NAME+'.sessionTutors', {
    sortBy: {property: 'date', direction: -1},
    filters: [
      {property: 'type', value: constants.TEACHER_TYPE, locked: true, hidden: true},
      {property: 'pending', value: false, locked: true, hidden: true}
    ]
  }, {
    invalidated: makeReducer(false, {
      [LOAD_COURSE]: () => true,
      [LOAD_COURSE_SESSION]: () => true,
      [SWITCH_PARTICIPANTS_VIEW]: () => true
    })
  }),
  sessionUsers: makeListReducer(selectors.STORE_NAME+'.sessionUsers', {
    sortBy: {property: 'date', direction: -1},
    filters: [
      {property: 'type', value: constants.LEARNER_TYPE, locked: true, hidden: true},
      {property: 'pending', value: false, locked: true, hidden: true}
    ]
  }, {
    invalidated: makeReducer(false, {
      [LOAD_COURSE]: () => true,
      [LOAD_COURSE_SESSION]: () => true,
      [SWITCH_PARTICIPANTS_VIEW]: () => true
    })
  }),
  sessionGroups: makeListReducer(selectors.STORE_NAME+'.sessionGroups', {
    sortBy: {property: 'date', direction: -1}
  }, {
    invalidated: makeReducer(false, {
      [LOAD_COURSE]: () => true,
      [LOAD_COURSE_SESSION]: () => true,
      [SWITCH_PARTICIPANTS_VIEW]: () => true
    })
  }),
  sessionPending: makeListReducer(selectors.STORE_NAME+'.sessionPending', {
    sortBy: {property: 'date', direction: -1},
    filters: [
      {property: 'type', value: constants.LEARNER_TYPE, locked: true, hidden: true},
      {property: 'pending', value: true, locked: true, hidden: true}
    ]
  }, {
    invalidated: makeReducer(false, {
      [LOAD_COURSE]: () => true,
      [LOAD_COURSE_SESSION]: () => true,
      [SWITCH_PARTICIPANTS_VIEW]: () => true
    })
  }),

  courseStats: makeReducer(null, {
    [LOAD_COURSE_STATS]: (state, action) => action.stats,
    [SWITCH_PARTICIPANTS_VIEW]: () => null
  })
})

export {
  reducer
}
