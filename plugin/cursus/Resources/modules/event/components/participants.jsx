import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import {schemeCategory20c} from 'd3-scale'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {AlertBlock} from '#/main/app/alert/components/alert-block'
import {Routes} from '#/main/app/router/components/routes'
import {Vertical} from '#/main/app/content/tabs/components/vertical'
import {MODAL_USERS} from '#/main/core/modals/users'
import {MODAL_GROUPS} from '#/main/core/modals/groups'

import {Event as EventTypes} from '#/plugin/cursus/prop-types'
import {constants} from '#/plugin/cursus/constants'
import {isFull} from '#/plugin/cursus/utils'

import {selectors} from '#/plugin/cursus/event/store'
import {SessionGroups} from '#/plugin/cursus/session/components/groups'
import {SessionUsers} from '#/plugin/cursus/session/components/users'

const EventUsers = (props) =>
  <SessionUsers
    session={props.event}
    name={props.name}
    url={['apiv2_cursus_event_list_users', {type: props.type, id: props.event.id}]}
    unregisterUrl={['apiv2_cursus_event_remove_users', {type: props.type, id: props.event.id}]}
    actions={(rows) => [
      {
        name: 'invite',
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-envelope',
        label: trans('send_invitation', {}, 'actions'),
        callback: () => props.inviteUsers(props.event.id, rows),
        displayed: hasPermission('edit', props.event)
      }
    ]}
    add={{
      name: 'add_users',
      type: MODAL_BUTTON,
      label: constants.TEACHER_TYPE === props.type ? trans('add_tutors', {}, 'cursus') : trans('add_users'),
      modal: [MODAL_USERS, {
        selectAction: (selected) => ({
          type: CALLBACK_BUTTON,
          label: trans('register', {}, 'actions'),
          callback: () => props.addUsers(props.event.id, selected, props.type)
        })
      }]
    }}
  />

EventUsers.propTypes = {
  name: T.string.isRequired,
  type: T.string.isRequired,
  event: T.shape(
    EventTypes.propTypes
  ),
  addUsers: T.func.isRequired,
  inviteUsers: T.func.isRequired
}

const EventGroups = (props) =>
  <SessionGroups
    session={props.event}
    name={props.name}
    url={['apiv2_cursus_event_list_groups', {type: props.type, id: props.event.id}]}
    unregisterUrl={['apiv2_cursus_event_remove_groups', {type: props.type, id: props.event.id}]}
    actions={(rows) => [
      {
        name: 'invite',
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-envelope',
        label: trans('send_invitation', {}, 'actions'),
        callback: () => props.inviteGroups(props.event.id, rows),
        displayed: hasPermission('edit', props.event)
      }
    ]}
    add={{
      name: 'add_groups',
      type: MODAL_BUTTON,
      label: trans('add_groups'),
      disabled: isFull(props.event),
      modal: [MODAL_GROUPS, {
        selectAction: (selected) => ({
          type: CALLBACK_BUTTON,
          label: trans('register', {}, 'actions'),
          callback: () => props.addGroups(props.event.id, selected, props.type)
        })
      }]
    }}
  />

EventGroups.propTypes = {
  name: T.string.isRequired,
  type: T.string.isRequired,
  event: T.shape(
    EventTypes.propTypes
  ),
  addGroups: T.func.isRequired,
  inviteGroups: T.func.isRequired
}

const EventParticipants = (props) =>
  <Fragment>
    <div className="row" style={{marginTop: '-20px'}}>
      <div className="analytics-card">
        <span className="fa fa-chalkboard-teacher" style={{backgroundColor: schemeCategory20c[1]}} />

        <h1 className="h3">
          <small>{trans('tutors', {}, 'cursus')}</small>
          {get(props.event, 'participants.tutors', 0)}
        </h1>
      </div>

      <div className="analytics-card">
        <span className="fa fa-user" style={{backgroundColor: schemeCategory20c[5]}} />

        <h1 className="h3">
          <small>{trans('users')}</small>
          {get(props.event, 'participants.learners', 0)}
        </h1>
      </div>

      <div className="analytics-card">
        <span className="fa fa-user-plus" style={{backgroundColor: schemeCategory20c[9]}} />

        <h1 className="h3">
          <small>{trans('available_seats', {}, 'cursus')}</small>
          {get(props.event, 'restrictions.users') ?
            (get(props.event, 'restrictions.users') - get(props.event, 'participants.learners', 0)) + ' / ' + get(props.event, 'restrictions.users')
            : <span className="fa fa-fw fa-infinity" />
          }
        </h1>
      </div>
    </div>

    <div className="row">
      <div className="col-md-3">
        <Vertical
          basePath={props.path+'/'+props.event.id+'/participants'}
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
            }
          ]}
        />
      </div>

      <div className="col-md-9">
        <Routes
          path={props.path+'/'+props.event.id+'/participants'}
          routes={[
            {
              path: '/',
              exact: true,
              render() {
                const Tutors = (
                  <EventUsers
                    type={constants.TEACHER_TYPE}
                    name={selectors.STORE_NAME+'.tutors'}
                    event={props.event}
                    addUsers={props.addUsers}
                    inviteUsers={props.inviteUsers}
                  />
                )

                return Tutors
              }
            }, {
              path: '/users',
              render() {
                const Users = (
                  <Fragment>
                    {isFull(props.event) &&
                      <AlertBlock type="warning" title={trans('La séance est complète.', {}, 'cursus')}>
                        {trans('Les inscriptions ne sont plus possible pour cette séance..', {}, 'cursus')}
                      </AlertBlock>
                    }

                    <EventUsers
                      type={constants.LEARNER_TYPE}
                      name={selectors.STORE_NAME+'.users'}
                      event={props.event}
                      addUsers={props.addUsers}
                      inviteUsers={props.inviteUsers}
                    />
                  </Fragment>
                )

                return Users
              }
            }, {
              path: '/groups',
              render() {
                const Groups = (
                  <EventGroups
                    type={constants.LEARNER_TYPE}
                    name={selectors.STORE_NAME+'.groups'}
                    event={props.event}
                    addGroups={props.addGroups}
                    inviteGroups={props.inviteGroups}
                  />
                )

                return Groups
              }
            }
          ]}
        />
      </div>
    </div>
  </Fragment>

EventParticipants.propTypes = {
  path: T.string.isRequired,
  event: T.shape(
    EventTypes.propTypes
  ).isRequired,
  addUsers: T.func.isRequired,
  inviteUsers: T.func.isRequired,
  addGroups: T.func.isRequired,
  inviteGroups: T.func.isRequired
}

export {
  EventParticipants
}
