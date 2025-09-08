import React, {useMemo} from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch, useSelector} from 'react-redux'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {selectors as securitySelectors} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {ListData} from '#/main/app/content/list/containers/data'
import {actions as listActions} from '#/main/app/content/list/store'

import {EventCard} from '#/plugin/cursus/event/components/card'
import {EventStatus} from '#/plugin/cursus/components/event-status'
import {constants as listConst} from '#/main/app/content/list/constants'
import {getActions, getDefaultAction} from '#/plugin/cursus/event/utils'

const EventList = (props) => {
  const dispatch = useDispatch()
  const currentUser = useSelector(securitySelectors.currentUser)

  const refresher = useMemo(() => ({
    add:    () => dispatch(listActions.invalidateData(props.name)),
    update: () => dispatch(listActions.invalidateData(props.name)),
    delete: () => dispatch(listActions.invalidateData(props.name))
  }), [props.path])

  return (
    <ListData
      primaryAction={(row) => getDefaultAction(row, refresher, props.path, currentUser)}
      actions={(rows) => getActions(rows, refresher, props.path, currentUser).then((actions) => [].concat(actions, props.customActions(rows)))}
      definition={[
        {
          name: 'status',
          type: 'choice',
          label: trans('status'),
          alias: 'plannedObject.status',
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
          render: (row) => <EventStatus startDate={get(row, 'start')} endDate={get(row, 'end')} />
        }, {
          name: 'name',
          type: 'string',
          label: trans('name'),
          displayed: true,
          filterable: false,
          primary: true
        }, {
          name: 'code',
          type: 'string',
          label: trans('code'),
          filterable: false,
          displayed: false
        }, {
          name: 'start',
          alias: 'plannedObject.startDate',
          type: 'date',
          label: trans('start_date'),
          displayed: true,
          options: {
            time: true
          }
        }, {
          name: 'end',
          alias: 'plannedObject.endDate',
          type: 'date',
          label: trans('end_date'),
          options: {
            time: true
          },
          displayed: true
        }, {
          name: 'location',
          type: 'location',
          alias: 'plannedObject.location',
          label: trans('location'),
          placeholder: trans('online_session', {}, 'cursus'),
          displayed: true,
          options: {multiple: false}
        }, {
          name: 'tutors',
          type: 'user',
          label: trans('tutors', {}, 'cursus'),
          options: {multiple: true},
          filterable: false
        }, {
          name: 'availableSeats',
          type: 'number',
          label: trans('available_seats', {}, 'cursus'),
          displayed: true,
          filterable: false,
          sortable: false,
          render: (row) => {
            if (get(row, 'restrictions.users')) {
              return (get(row, 'restrictions.users') - get(row, 'participants.learners', 0)) + ' / ' + get(row, 'restrictions.users')
            }

            return (
              <>
                <span className="visually-hidden">{trans('not_limited', {}, 'cursus')}</span>
                <span className="fa fa-infinity" aria-hidden={true} />
              </>
            )
          }
        }, {
          name: 'capacity',
          type: 'choice',
          label: trans('available_seats', {}, 'cursus'),
          options: {
            choices: {
              available_seats: trans('available_seats', {}, 'cursus'),
              full: trans('full', {}, 'cursus'),
              missing_seats: trans('missing_seats', {}, 'cursus')
            }
          },
          displayable: false,
          sortable: false,
          filterable: true
        }
      ].concat(props.customDefinition || [])}
      display={{
        current: listConst.DISPLAY_LIST
      }}

      {...omit(props, 'path', 'url', 'autoload', 'customDefinition', 'customActions', 'invalidate')}

      name={props.name}
      fetch={{
        url: props.url,
        autoload: true
      }}
      card={EventCard}
    />
  )
}

EventList.propTypes = {
  path: T.string.isRequired,
  name: T.string.isRequired,
  url: T.oneOfType([T.string, T.array]).isRequired,
  autoload: T.bool,
  customDefinition: T.arrayOf(T.shape({
    // data list prop types
  })),
  customActions: T.func
}

EventList.defaultProps = {
  autoload: true,
  customActions: () => []
}

export {
  EventList
}
