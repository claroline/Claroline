import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {trans, now} from '#/main/app/intl'
import {param} from '#/main/app/config'
import {hasPermission} from '#/main/app/security'
import {LINK_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {constants as listConst} from '#/main/app/content/list/constants'

import {route} from '#/plugin/cursus/routing'
import {SessionCard} from '#/plugin/cursus/session/components/card'

const SessionList = (props) =>
  <ListData
    name={props.name}
    fetch={{
      url: props.url,
      autoload: true
    }}
    primaryAction={(row) => ({
      type: LINK_BUTTON,
      target: route(props.path, row.course, row),
      label: trans('open', {}, 'actions')
    })}
    delete={props.delete}
    definition={[
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
            <span className={classes('label', {
              'label-success': 'not_started' === status,
              'label-info': 'in_progress' === status,
              'label-danger': 'ended' === status
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
        sortable: false
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
        name: 'availableSeats',
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
        name: 'pricing.price',
        alias: 'price',
        label: trans('price'),
        type: 'currency',
        displayable: param('pricing.enabled'),
        displayed: param('pricing.enabled'),
        filterable: param('pricing.enabled'),
        sortable: param('pricing.enabled')
      }, {
        name: 'display.order',
        alias: 'order',
        type: 'number',
        label: trans('order'),
        displayable: false,
        filterable: false
      }
    ].concat(props.definition)}
    card={SessionCard}
    actions={(rows) => {
      let actions = [
        {
          name: 'export-pdf',
          type: URL_BUTTON,
          icon: 'fa fa-fw fa-file-pdf-o',
          label: trans('export-pdf', {}, 'actions'),
          displayed: hasPermission('open', rows[0]),
          group: trans('transfer'),
          target: ['apiv2_cursus_session_download_pdf', {id: rows[0].id}],
          scope: ['object']
        }
      ]

      if (props.actions) {
        actions = [].concat(actions, props.actions(rows))
      }

      return actions
    }}
    display={{
      current: listConst.DISPLAY_LIST
    }}
  />

SessionList.propTypes = {
  path: T.string.isRequired,
  name: T.string.isRequired,
  url: T.oneOfType([T.string, T.array]).isRequired,
  delete: T.object,
  definition: T.arrayOf(T.shape({
    // TODO : list property propTypes
  })),
  actions: T.func
}

SessionList.defaultProps = {
  definition: []
}

export {
  SessionList
}
