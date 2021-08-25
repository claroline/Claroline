import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {now} from '#/main/app/intl/date'
import {hasPermission} from '#/main/app/security'
import {LINK_BUTTON, MODAL_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {constants as listConst} from '#/main/app/content/list/constants'

import {constants} from '#/plugin/cursus/constants'
import {EventCard} from '#/plugin/cursus/event/components/card'
import {MODAL_TRAINING_EVENT_ABOUT} from '#/plugin/cursus/event/modals/about'
import {MODAL_TRAINING_EVENT_PARAMETERS} from '#/plugin/cursus/event/modals/parameters'

const EventList = (props) =>
  <ListData
    name={props.name}
    fetch={{
      url: props.url,
      autoload: true
    }}
    primaryAction={props.primaryAction || ((row) => ({
      type: LINK_BUTTON,
      target: props.path+'/'+row.id,
      label: trans('open', {}, 'actions')
    }))}
    actions={(rows) => [
      {
        name: 'about',
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-info',
        label: trans('show-info', {}, 'actions'),
        modal: [MODAL_TRAINING_EVENT_ABOUT, {
          event: rows[0]
        }],
        scope: ['object']
      }, {
        name: 'edit',
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-pencil',
        label: trans('edit', {}, 'actions'),
        modal: [MODAL_TRAINING_EVENT_PARAMETERS, {
          event: rows[0],
          onSave: props.invalidate
        }],
        scope: ['object'],
        group: trans('management'),
        displayed: hasPermission('edit', rows[0])
      }, {
        name: 'export-pdf',
        type: URL_BUTTON,
        icon: 'fa fa-fw fa-file-pdf-o',
        label: trans('export-pdf', {}, 'actions'),
        displayed: hasPermission('open', rows[0]),
        scope: ['object'],
        group: trans('transfer'),
        target: ['apiv2_cursus_event_download_pdf', {id: rows[0].id}]
      }, {
        name: 'export-ics',
        type: URL_BUTTON,
        icon: 'fa fa-fw fa-calendar',
        label: trans('export-ics', {}, 'actions'),
        displayed: hasPermission('open', rows[0]),
        scope: ['object'],
        group: trans('transfer'),
        target: ['apiv2_cursus_event_download_ics', {id: rows[0].id}]
      }, {
        name: 'export-presences-empty',
        type: URL_BUTTON,
        icon: 'fa fa-fw fa-border-none',
        label: trans('export-presences-empty', {}, 'cursus'),
        displayed: hasPermission('edit', rows[0]),
        group: trans('presences', {}, 'cursus'),
        target: ['apiv2_cursus_event_presence_download', {id: rows[0].id, filled: 0}]
      }, {
        name: 'export-presences-filled',
        type: URL_BUTTON,
        icon: 'fa fa-fw fa-border-all',
        label: trans('export-presences-filled', {}, 'cursus'),
        displayed: hasPermission('edit', rows[0]),
        group: trans('presences', {}, 'cursus'),
        target: ['apiv2_cursus_event_presence_download', {id: rows[0].id, filled: 1}]
      }
    ].concat(props.actions(rows))}
    delete={{
      url: ['apiv2_cursus_event_delete_bulk'],
      displayed: (rows) => -1 !== rows.findIndex(row => hasPermission('delete', row))
    }}
    definition={[
      {
        name: 'status',
        type: 'choice',
        label: trans('status'),
        sortable: false,
        displayed: true,
        filterable: true,
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
          if (get(row, 'start') > now(false)) {
            status = 'not_started'
          } else if (get(row, 'start') <= now(false) && get(row, 'end') >= now(false)) {
            status = 'in_progress'
          } else if (get(row, 'end') < now(false)) {
            status = 'ended'
          }

          const EventStatus = (
            <span className={classes('label', {
              'label-success': 'not_started' === status,
              'label-info': 'in_progress' === status,
              'label-danger': 'ended' === status
            })}>
              {trans('session_'+status, {}, 'cursus')}
            </span>
          )

          return EventStatus
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
        name: 'location',
        type: 'location',
        label: trans('location'),
        placeholder: trans('online_session', {}, 'cursus'),
        displayed: true
      }, {
        name: 'start',
        alias: 'startDate',
        type: 'date',
        label: trans('start_date'),
        displayed: true,
        options: {
          time: true
        }
      }, {
        name: 'end',
        alias: 'endDate',
        type: 'date',
        label: trans('end_date'),
        options: {
          time: true
        },
        displayed: true
      }, {
        name: 'tutors',
        type: 'users',
        label: trans('tutors', {}, 'cursus')
      }, {
        name: 'restrictions.users',
        alias: 'maxUsers',
        type: 'number',
        label: trans('max_participants', {}, 'cursus'),
        displayed: true
      }, {
        name: 'registration.registrationType',
        alias: 'registrationType',
        type: 'choice',
        label: trans('registration'),
        displayed: false,
        options: {
          choices: constants.REGISTRATION_TYPES
        }
      }
    ].concat(props.definition)}
    card={EventCard}
    display={{
      current: listConst.DISPLAY_LIST
    }}
  />

EventList.propTypes = {
  path: T.string.isRequired,
  name: T.string.isRequired,
  url: T.oneOfType([T.string, T.array]).isRequired,
  definition: T.arrayOf(T.shape({
    // TODO : list property propTypes
  })),
  primaryAction: T.func,
  actions: T.func,
  invalidate: T.func.isRequired
}

EventList.defaultProps = {
  actions: () => [],
  definition: []
}

export {
  EventList
}
