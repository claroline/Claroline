import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Routes} from '#/main/app/router/components/routes'
import {Vertical} from '#/main/app/content/tabs/components/vertical'
import {ContentInfoBlocks} from '#/main/app/content/components/info-block'
import {MODAL_USERS} from '#/main/community/modals/users'
import {MODAL_GROUPS} from '#/main/community/modals/groups'

import {selectors} from '#/plugin/cursus/tools/trainings/catalog/store/selectors'
import {Course as CourseTypes, Session as SessionTypes} from '#/plugin/cursus/prop-types'
import {constants} from '#/plugin/cursus/constants'

import {CourseStats} from '#/plugin/cursus/course/components/stats'
import {SessionGroups} from '#/plugin/cursus/session/containers/groups'
import {SessionUsers} from '#/plugin/cursus/session/containers/users'
import {Button} from '#/main/app/action'
import {MODAL_TRAINING_SESSIONS} from '#/plugin/cursus/modals/sessions'

const CourseParticipants = (props) =>
  <>
    <ContentInfoBlocks
      className="my-4"
      size="lg"
      items={[
        {
          icon: 'fa fa-chalkboard-teacher',
          label: trans('tutors', {}, 'cursus'),
          value: get(props.course, 'participants.tutors', 0)
        }, {
          icon: 'fa fa-user',
          label: trans('users'),
          value: get(props.course, 'participants.learners', 0)
        }, {
          icon: 'fa fa-hourglass-half',
          label: trans('pending'),
          value: get(props.course, 'participants.pending', 0)
        }, {
          icon: 'fa fa-user-plus',
          label: trans('available_seats_per_session', {}, 'cursus'),
          value: get(props.course, 'restrictions.users') ?
            get(props.course, 'restrictions.users')
            : <span className="fa fa-fw fa-infinity" />
        }
      ]}
    />

    <div className="row">
      <div className="col-md-3">
        <Vertical
          className="mb-3"
          basePath={props.path}
          tabs={[
            {
              icon: 'fa fa-fw fa-chalkboard-teacher',
              title: trans('tutors', {}, 'cursus'),
              path: '/',
              exact: true
            }, {
              icon: 'fa fa-fw fa-user',
              title: trans('users'),
              path: '/users'
            }, {
              icon: 'fa fa-fw fa-users',
              title: trans('groups'),
              path: '/groups'
            }, {
              icon: 'fa fa-fw fa-hourglass-half',
              title: trans('pending'),
              path: '/pending'
            }, {
              icon: 'fa fa-fw fa-pie-chart',
              title: trans('statistics'),
              path: '/stats',
              displayed: !!get(props.course, 'registration.form')
            }
          ]}
        />

        {props.activeSession &&
          <Button
            className="btn btn-outline-secondary w-100 mb-3"
            type={CALLBACK_BUTTON}
            label="Voir pour la session ouverte"
            callback={props.toggleVisibility}
          />
        }
      </div>

      <div className="col-md-9">
        <Routes
          path={props.path}
          routes={[
            {
              path: '/',
              exact: true,
              render: () => (
                <SessionUsers
                  type={constants.TEACHER_TYPE}
                  course={props.course}
                  name={selectors.STORE_NAME+'.sessionTutors'}
                  customDefinition={[
                    {
                      name: 'session',
                      label: trans('session', {}, 'cursus'),
                      type: 'training_session',
                      displayed: true,
                      displayable: true,
                      filterable: true,
                      options: {
                        course: props.course,
                        picker: {
                          url: ['apiv2_cursus_course_list_sessions', {id: get(props.course, 'id')}],
                          filters: [{property: 'status', value: 'not_ended'}]
                        }
                      }
                    }
                  ]}
                  add={{
                    name: 'add_tutors',
                    type: MODAL_BUTTON,
                    label: trans('add_tutors', {}, 'cursus'),
                    modal: [MODAL_USERS, {
                      selectAction: (selected) => ({
                        type: MODAL_BUTTON,
                        label: trans('register', {}, 'actions'),
                        modal: [MODAL_TRAINING_SESSIONS, {
                          url: ['apiv2_cursus_course_list_sessions', {id: get(props.course, 'id')}],
                          filters: [{property: 'status', value: 'not_ended'}],
                          selectAction: (selectedSessions) => ({
                            type: CALLBACK_BUTTON,
                            label: trans('register', {}, 'actions'),
                            callback: () => selectedSessions.map(selectedSession => props.addUsers(selectedSession.id, selected, constants.TEACHER_TYPE))
                          })
                        }]
                      })
                    }]
                  }}
                />
              )
            }, {
              path: '/users',
              render: () => (
                <SessionUsers
                  type={constants.LEARNER_TYPE}
                  course={props.course}
                  name={selectors.STORE_NAME+'.sessionUsers'}
                  customDefinition={[
                    {
                      name: 'session',
                      label: trans('session', {}, 'cursus'),
                      type: 'training_session',
                      displayed: true,
                      displayable: true,
                      filterable: true,
                      options: {
                        course: props.course,
                        picker: {
                          url: ['apiv2_cursus_course_list_sessions', {id: get(props.course, 'id')}],
                          filters: [{property: 'status', value: 'not_ended'}]
                        }
                      }
                    }
                  ]}
                  add={{
                    name: 'add_users',
                    type: MODAL_BUTTON,
                    label: trans('add_users'),
                    modal: [MODAL_USERS, {
                      selectAction: (selected) => ({
                        type: MODAL_BUTTON,
                        label: trans('register', {}, 'actions'),
                        modal: [MODAL_TRAINING_SESSIONS, {
                          url: ['apiv2_cursus_course_list_sessions', {id: get(props.course, 'id')}],
                          filters: [{property: 'status', value: 'not_ended'}],
                          selectAction: (selectedSessions) => ({
                            type: CALLBACK_BUTTON,
                            label: trans('register', {}, 'actions'),
                            callback: () => selectedSessions.map(selectedSession => props.addUsers(selectedSession.id, selected, constants.LEARNER_TYPE))
                          })
                        }]
                      })
                    }]
                  }}
                />
              )
            }, {
              path: '/groups',
              render: () => (
                <SessionGroups
                  type={constants.LEARNER_TYPE}
                  course={props.course}
                  name={selectors.STORE_NAME+'.sessionGroups'}
                  customDefinition={[
                    {
                      name: 'session',
                      label: trans('session', {}, 'cursus'),
                      type: 'training_session',
                      displayed: true,
                      displayable: true,
                      filterable: true,
                      options: {
                        course: props.course,
                        picker: {
                          url: ['apiv2_cursus_course_list_sessions', {id: get(props.course, 'id')}],
                          filters: [{property: 'status', value: 'not_ended'}]
                        }
                      }
                    }
                  ]}
                  add={{
                    name: 'add_groups',
                    type: MODAL_BUTTON,
                    label: trans('add_groups'),
                    modal: [MODAL_GROUPS, {
                      selectAction: (selected) => ({
                        type: MODAL_BUTTON,
                        label: trans('register', {}, 'actions'),
                        modal: [MODAL_TRAINING_SESSIONS, {
                          url: ['apiv2_cursus_course_list_sessions', {id: get(props.course, 'id')}],
                          filters: [{property: 'status', value: 'not_ended'}],
                          selectAction: (selectedSessions) => ({
                            type: CALLBACK_BUTTON,
                            label: trans('register', {}, 'actions'),
                            callback: () => selectedSessions.map(selectedSession => props.addGroups(selectedSession.id, selected, constants.LEARNER_TYPE))
                          })
                        }]
                      })
                    }]
                  }}
                />
              )
            }, {
              path: '/pending',
              render: () => (
                <SessionUsers
                  type={constants.LEARNER_TYPE}
                  course={props.course}
                  session={props.activeSession}
                  name={selectors.STORE_NAME+'.sessionPending'}
                  customDefinition={[
                    {
                      name: 'session',
                      label: trans('session', {}, 'cursus'),
                      type: 'training_session',
                      displayed: true,
                      displayable: true,
                      filterable: true,
                      options: {
                        course: props.course,
                        picker: {
                          url: ['apiv2_cursus_course_list_sessions', {id: get(props.course, 'id')}],
                          filters: [{property: 'status', value: 'not_ended'}]
                        }
                      }
                    }, {
                      name: 'confirmed',
                      type: 'boolean',
                      label: trans('confirmed'),
                      displayable: true,
                      displayed: false
                    }, {
                      name: 'validated',
                      type: 'boolean',
                      label: trans('validated'),
                      displayable: true,
                      displayed: false
                    }
                  ]}
                  add={{
                    name: 'add_pending',
                    type: MODAL_BUTTON,
                    label: trans('add_pending', {}, 'cursus'),
                    modal: [MODAL_USERS, {
                      selectAction: (selected) => ({
                        type: MODAL_BUTTON,
                        label: trans('register', {}, 'actions'),
                        modal: [MODAL_TRAINING_SESSIONS, {
                          url: ['apiv2_cursus_course_list_sessions', {id: get(props.course, 'id')}],
                          filters: [{property: 'status', value: 'not_ended'}],
                          selectAction: (selectedSessions) => ({
                            type: CALLBACK_BUTTON,
                            label: trans('register', {}, 'actions'),
                            callback: () => selectedSessions.map(selectedSession => props.addPending(selectedSession.id, selected))
                          })
                        }]
                      })
                    }]
                  }}
                />
              )
            }, {
              path: '/stats',
              onEnter: () => props.loadStats(props.course.id),
              disabled: !get(props.course, 'registration.form'),
              render: () => (
                <CourseStats
                  course={props.course}
                  stats={props.stats}
                />
              )
            }
          ]}
        />
      </div>
    </div>
  </>

CourseParticipants.propTypes = {
  path: T.string.isRequired,
  course: T.shape(
    CourseTypes.propTypes
  ).isRequired,
  activeSession: T.shape(
    SessionTypes.propTypes
  ),
  stats: T.object,
  addUsers: T.func.isRequired,
  addPending: T.func.isRequired,
  addGroups: T.func.isRequired,
  loadStats: T.func.isRequired,
  toggleVisibility: T.func.isRequired
}

export {
  CourseParticipants
}