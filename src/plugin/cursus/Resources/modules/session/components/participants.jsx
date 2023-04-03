import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import {schemeCategory20c} from 'd3-scale'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {LinkButton} from '#/main/app/buttons/link'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {AlertBlock} from '#/main/app/alert/components/alert-block'
import {Routes} from '#/main/app/router/components/routes'
import {Vertical} from '#/main/app/content/tabs/components/vertical'
import {ContentCounter} from '#/main/app/content/components/counter'
import {MODAL_USERS} from '#/main/community/modals/users'
import {MODAL_GROUPS} from '#/main/community/modals/groups'

import {selectors} from '#/plugin/cursus/tools/trainings/catalog/store/selectors'
import {Course as CourseTypes, Session as SessionTypes} from '#/plugin/cursus/prop-types'
import {constants} from '#/plugin/cursus/constants'
import {isFull} from '#/plugin/cursus/utils'

import {CourseStats} from '#/plugin/cursus/course/components/stats'
import {SessionGroups} from '#/plugin/cursus/session/containers/groups'
import {SessionUsers} from '#/plugin/cursus/session/containers/users'


const SessionParticipants = (props) =>
  <Fragment>
    <div className="row" style={{marginTop: -20}}>
      <ContentCounter
        icon="fa fa-chalkboard-teacher"
        label={trans('tutors', {}, 'cursus')}
        color={schemeCategory20c[1]}
        value={get(props.activeSession, 'participants.tutors', 0)}
      />

      <ContentCounter
        icon="fa fa-user"
        label={trans('users')}
        color={schemeCategory20c[5]}
        value={get(props.activeSession, 'participants.learners', 0)}
      />

      <ContentCounter
        icon="fa fa-hourglass-half"
        label={trans('En attente')}
        color={schemeCategory20c[9]}
        value={get(props.activeSession, 'participants.pending', 0)}
      />

      <ContentCounter
        icon="fa fa-user-plus"
        label={trans('available_seats', {}, 'cursus')}
        color={schemeCategory20c[13]}
        value={get(props.activeSession, 'restrictions.users') ?
          (get(props.activeSession, 'restrictions.users') - get(props.activeSession, 'participants.learners', 0)) + ' / ' + get(props.activeSession, 'restrictions.users')
          : <span className="fa fa-fw fa-infinity" />
        }
      />
    </div>

    <div className="row">
      <div className="col-md-3">
        <Vertical
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
              title: trans('En attente'),
              path: '/pending'
            }, {
              icon: 'fa fa-fw fa-pie-chart',
              title: trans('statistics'),
              path: '/stats'
            }
          ]}
        />

        <Button
          className="btn btn-link btn-block"
          type={CALLBACK_BUTTON}
          label="Voir pour toute la formation"
          callback={props.toggleVisibility}
          primary={true}
        />
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
                  session={props.activeSession}
                  name={selectors.STORE_NAME+'.sessionTutors'}
                  add={{
                    name: 'add_users',
                    type: MODAL_BUTTON,
                    label: trans('add_tutors', {}, 'cursus'),
                    modal: [MODAL_USERS, {
                      selectAction: (selected) => ({
                        type: CALLBACK_BUTTON,
                        label: trans('register', {}, 'actions'),
                        callback: () => props.addUsers(props.activeSession.id, selected, constants.TEACHER_TYPE)
                      })
                    }]
                  }}
                />
              )
            }, {
              path: '/users',
              render: () => (
                <Fragment>
                  {isFull(props.activeSession) &&
                    <AlertBlock type="warning" title={trans('session_full', {}, 'cursus')}>
                      {trans('session_full_help', {}, 'cursus')}
                    </AlertBlock>
                  }

                  {get(props.activeSession, 'registration.userValidation') &&
                    <AlertBlock title={trans('registration_user_confirmation_title', {}, 'cursus')}>
                      {trans('registration_user_confirmation_pending_help', {}, 'cursus')}
                      <br/>
                      {trans('registration_user_confirmation_manager_help', {}, 'cursus')}
                      (<LinkButton target={props.path+'/pending'}>{trans('show_pending_list', {}, 'cursus')}</LinkButton>)
                    </AlertBlock>
                  }

                  <SessionUsers
                    type={constants.LEARNER_TYPE}
                    course={props.course}
                    session={props.activeSession}
                    name={selectors.STORE_NAME+'.sessionUsers'}
                    add={{
                      name: 'add_users',
                      type: MODAL_BUTTON,
                      label: trans('add_users'),
                      modal: [MODAL_USERS, {
                        selectAction: (selected) => ({
                          type: CALLBACK_BUTTON,
                          label: trans('register', {}, 'actions'),
                          callback: () => props.addUsers(props.activeSession.id, selected, constants.LEARNER_TYPE)
                        })
                      }]
                    }}
                  />
                </Fragment>
              )
            }, {
              path: '/groups',
              render: () => (
                <SessionGroups
                  type={constants.LEARNER_TYPE}
                  course={props.course}
                  session={props.activeSession}
                  name={selectors.STORE_NAME+'.sessionGroups'}
                  add={{
                    name: 'add_groups',
                    type: MODAL_BUTTON,
                    label: trans('add_groups'),
                    modal: [MODAL_GROUPS, {
                      selectAction: (selected) => ({
                        type: CALLBACK_BUTTON,
                        label: trans('register', {}, 'actions'),
                        callback: () => props.addGroups(props.activeSession.id, selected, props.type)
                      })
                    }]
                  }}
                />
              )
            }, {
              path: '/pending',
              render: () => (
                <Fragment>
                  {isFull(props.activeSession) && hasPermission('register', props.activeSession) &&
                    <AlertBlock type="warning" title={trans('session_full', {}, 'cursus')}>
                      {trans('session_full_pending_help', {}, 'cursus')}
                    </AlertBlock>
                  }

                  <SessionUsers
                    type={constants.LEARNER_TYPE}
                    course={props.course}
                    session={props.activeSession}
                    name={selectors.STORE_NAME+'.sessionPending'}
                    customDefinition={[
                      {
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
                      name: 'add_users',
                      type: MODAL_BUTTON,
                      label: trans('add_pending', {}, 'cursus'),
                      modal: [MODAL_USERS, {
                        selectAction: (selected) => ({
                          type: CALLBACK_BUTTON,
                          label: trans('register', {}, 'actions'),
                          callback: () => props.addPending(props.activeSession.id, selected)
                        })
                      }]
                    }}
                  />
                </Fragment>
              )
            }, {
              path: '/stats',
              onEnter: () => props.loadStats(props.course.id, get(props.activeSession, 'id')),
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
  </Fragment>

SessionParticipants.propTypes = {
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
  SessionParticipants
}