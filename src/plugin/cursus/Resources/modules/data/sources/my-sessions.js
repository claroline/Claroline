import React from 'react'
import classes from 'classnames'
import get from 'lodash/get'

import {trans, now} from '#/main/app/intl'
import {URL_BUTTON} from '#/main/app/buttons'
import {route as workspaceRoute} from '#/main/core/workspace/routing'

import {SessionCard} from '#/plugin/cursus/session/components/card'

export default {
  name: 'my-sessions',
  icon: 'fa fa-fw fa-calendar-week',
  parameters: {
    primaryAction: (session) => ({
      type: URL_BUTTON,
      target: `#${workspaceRoute(session.workspace)}`
    }),
    definition: [
      {
        name: 'status',
        type: 'choice',
        label: trans('status'),
        displayed: true,
        order: 1,
        options: {
          noEmpty: true,
          choices: {
            not_started: trans('session_not_started', {}, 'cursus'),
            in_progress: trans('session_in_progress', {}, 'cursus'),
            ended: trans('session_ended', {}, 'cursus'),
            not_ended: trans('session_not_ended', {}, 'cursus')
          }
        },
        render: (row) => {
          let status
          if (get(row, 'restrictions.dates[0]') > now(false)) {
            status = 'not_started'
          } else if (get(row, 'restrictions.dates[0]') <= now(false) && get(row, 'restrictions.dates[1]') >= now(false)) {
            status = 'in_progress'
          } else if (get(row, 'restrictions.dates[1]') < now(false)) {
            status = 'ended'
          }

          const SessionStatus = (
            <span className={classes('badge', {
              'text-bg-success': 'not_started' === status,
              'text-bg-info': 'in_progress' === status,
              'text-bg-danger': 'ended' === status
            })}>
              {trans('session_'+status, {}, 'cursus')}
            </span>
          )

          return SessionStatus
        }
      }, {
        name: 'name',
        type: 'string',
        label: trans('name'),
        displayed: true,
        primary: true
      }, {
        name: 'code',
        type: 'string',
        label: trans('code'),
        displayed: false
      }, {
        name: 'course',
        type: 'training_course',
        label: trans('course', {}, 'cursus'),
        displayed: true
      }, {
        name: 'location',
        type: 'location',
        label: trans('location'),
        placeholder: trans('online_session', {}, 'cursus'),
        displayed: true
      }, {
        name: 'restrictions.dates[0]',
        alias: 'startDate',
        type: 'date',
        label: trans('start_date'),
        displayed: true
      }, {
        name: 'restrictions.dates[1]',
        alias: 'endDate',
        type: 'date',
        label: trans('end_date'),
        displayed: true
      }, {
        name: 'workspace',
        type: 'workspace',
        label: trans('workspace'),
        sortable: false
      }, {
        name: 'restrictions.users', // for retro compatibility with existing data sources
        alias: 'maxUsers', // for retro compatibility with existing data sources
        type: 'string',
        label: trans('available_seats', {}, 'cursus'),
        calculated: (row) => {
          if (get(row, 'restrictions.users')) {
            return (get(row, 'restrictions.users') - get(row, 'participants.learners', 0)) + ' / ' + get(row, 'restrictions.users')
          }

          return trans('not_limited', {}, 'cursus')
        },
        displayed: true,
        filterable: false,
        sortable: false
      }, {
        name: 'registration.selfRegistration',
        alias: 'publicRegistration',
        type: 'boolean',
        label: trans('public_registration'),
        displayed: false
      }, {
        name: 'registration.selfUnRegistration',
        alias: 'publicUnregistration',
        type: 'boolean',
        label: trans('public_unregistration'),
        displayed: false
      }, {
        name: 'registration.validation',
        alias: 'registrationValidation',
        type: 'boolean',
        label: trans('registration_validation', {}, 'cursus'),
        displayed: false
      }, {
        name: 'registration.userValidation',
        alias: 'userValidation',
        type: 'boolean',
        label: trans('user_validation', {}, 'cursus'),
        displayed: false
      }, {
        name: 'courseTags',
        type: 'tag',
        label: trans('tags'),
        displayed: false,
        displayable: false,
        sortable: false,
        options: {
          objectClass: 'Claroline\\CursusBundle\\Entity\\Course'
        }
      }
    ],
    card: SessionCard
  }
}
