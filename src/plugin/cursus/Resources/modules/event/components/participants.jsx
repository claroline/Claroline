import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'
import {schemeCategory20c} from 'd3-scale'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {CALLBACK_BUTTON, DOWNLOAD_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {AlertBlock} from '#/main/app/alert/components/alert-block'
import {Routes} from '#/main/app/router/components/routes'
import {ListData} from '#/main/app/content/list/containers/data'
import {Vertical} from '#/main/app/content/tabs/components/vertical'
import {MODAL_USERS} from '#/main/core/modals/users'
import {MODAL_GROUPS} from '#/main/core/modals/groups'

import {Event as EventTypes} from '#/plugin/cursus/prop-types'
import {constants} from '#/plugin/cursus/constants'
import {isFull} from '#/plugin/cursus/utils'

import {selectors} from '#/plugin/cursus/event/store'
import {MODAL_EVENT_PRESENCE} from '#/plugin/cursus/event/modals/presence'
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
        displayed: hasPermission('register', props.event)
      }, {
        name: 'download-presence',
        type: DOWNLOAD_BUTTON,
        icon: 'fa fa-fw fa-file-pdf-o',
        label: trans('download_presence', {}, 'cursus'),
        file: {
          url: ['apiv2_cursus_user_presence_download', {id: props.event.id, userId: rows[0].user.id}]
        },
        scope: ['object']
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
        displayed: hasPermission('register', props.event)
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

const EventPresences = (props) =>
  <ListData
    name={selectors.STORE_NAME+'.presences'}
    fetch={{
      url: ['apiv2_cursus_event_presence_list', {id: props.event.id}],
      autoload: true
    }}
    definition={[
      {
        name: 'user',
        type: 'user',
        label: trans('user'),
        displayed: true
      }, {
        name: 'status',
        type: 'choice',
        label: trans('status'),
        options: {
          choices: constants.PRESENCE_STATUSES
        },
        render: (row) => (
          <span className={classes('label', `label-${constants.PRESENCE_STATUS_COLORS[row.status]}`)}>
            {constants.PRESENCE_STATUSES[row.status]}
          </span>
        ),
        displayed: true
      }
    ]}
    actions={(rows) => [
      {
        name: 'edit',
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-pencil',
        label: trans('edit', {}, 'actions'),
        displayed: hasPermission('edit', props.event),
        modal: [MODAL_EVENT_PRESENCE, {
          changeStatus: (status) => props.setPresenceStatus(props.event.id, rows, status)
        }]
      }, {
        name: 'download-presence',
        type: DOWNLOAD_BUTTON,
        icon: 'fa fa-fw fa-file-pdf-o',
        label: trans('download_presence', {}, 'cursus'),
        file: {
          url: ['apiv2_cursus_user_presence_download', {id: props.event.id, userId: rows[0].user.id}]
        },
        scope: ['object']
      }
    ]}
  />

EventPresences.propTypes = {
  event: T.shape(
    EventTypes.propTypes
  ),
  setPresenceStatus: T.func.isRequired
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
            }, {
              icon: 'fa fa-fw fa-user-check',
              title: trans('presences', {}, 'cursus'),
              path: '/presences'
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
              render: () => (
                <EventUsers
                  type={constants.TEACHER_TYPE}
                  name={selectors.STORE_NAME+'.tutors'}
                  event={props.event}
                  addUsers={props.addUsers}
                  inviteUsers={props.inviteUsers}
                />
              )
            }, {
              path: '/users',
              render: () => (
                <Fragment>
                  {isFull(props.event) &&
                    <AlertBlock type="warning" title={trans('event_full', {}, 'cursus')}>
                      {trans('event_full_help', {}, 'cursus')}
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
            }, {
              path: '/groups',
              render: () => (
                <EventGroups
                  type={constants.LEARNER_TYPE}
                  name={selectors.STORE_NAME+'.groups'}
                  event={props.event}
                  addGroups={props.addGroups}
                  inviteGroups={props.inviteGroups}
                />
              )
            }, {
              path: '/presences',
              render: () => (
                <EventPresences
                  event={props.event}
                  setPresenceStatus={props.setPresenceStatus}
                />
              )
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
  inviteGroups: T.func.isRequired,
  setPresenceStatus: T.func.isRequired
}

export {
  EventParticipants
}
