import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {URL_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {constants} from '#/plugin/cursus/constants'
import {EventCard} from '#/plugin/cursus/event/components/card'

const EventList = (props) =>
  <ListData
    name={props.name}
    fetch={{
      url: props.url,
      autoload: true
    }}
    actions={(rows) => [
      {
        name: 'export-pdf',
        type: URL_BUTTON,
        icon: 'fa fa-fw fa-file-pdf-o',
        label: trans('export-pdf', {}, 'actions'),
        displayed: hasPermission('open', rows[0]),
        scope: ['object'],
        group: trans('transfer'),
        target: ['apiv2_cursus_event_download_pdf', {id: rows[0].id}]
      }
    ].concat(props.actions(rows))}
    delete={{
      url: ['apiv2_cursus_event_delete_bulk'],
      displayed: (rows) => -1 !== rows.findIndex(row => hasPermission('delete', row))
    }}
    primaryAction={props.primaryAction}
    definition={[
      {
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
        name: 'restrictions.dates[0]',
        alias: 'startDate',
        type: 'date',
        label: trans('start_date'),
        displayed: true,
        options: {
          time: true
        }
      }, {
        name: 'restrictions.dates[1]',
        alias: 'endDate',
        type: 'date',
        label: trans('end_date'),
        options: {
          time: true
        },
        displayed: true
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
    ]}
    card={EventCard}
  />

EventList.propTypes = {
  name: T.string.isRequired,
  url: T.oneOfType([T.string, T.array]).isRequired,

  primaryAction: T.func,
  actions: T.func
}

EventList.defaultProps = {
  actions: () => []
}

export {
  EventList
}
