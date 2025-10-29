import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {ListData, constants as listConst} from '#/main/app/content/list'

import {EventCard} from '#/plugin/agenda/event/components/card'
import {EventIcon} from '#/plugin/agenda/event/components/icon'

import {EventStatus} from '#/plugin/agenda/event/components/status'

const EventList = (props) =>
  <ListData
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
            not_started: trans('not_started'),
            in_progress: trans('in_progress'),
            ended: trans('ended'),
            not_ended: trans('not_ended')
          }
        },
        render: (row) => <EventStatus startDate={get(row, 'start')} endDate={get(row, 'end')} />
      }, {
        name: 'name',
        type: 'string',
        label: trans('name'),
        displayed: true,
        primary: true,
        render: (event) => (
          <div className="d-flex flex-direction-row gap-3 align-items-center" role="presentation">
            <EventIcon type={event.meta.type} />
            {event.name}
          </div>
        )
      }, {
        name: 'description',
        type: 'html',
        label: trans('description'),
        displayed: true
      }, {
        name: 'start',
        type: 'date',
        alias: 'startDate',
        label: trans('start_date'),
        displayed: true,
        options: {time: true}
      }, {
        name: 'end',
        type: 'date',
        alias: 'endDate',
        label: trans('end_date'),
        displayed: true,
        options: {time: true}
      }
    ].concat(props.customDefinition)}
    {...omit(props, 'url', 'autoload', 'customDefinition')}

    name={props.name}
    fetch={{
      url: props.url,
      autoload: props.autoload
    }}
    card={EventCard}
    display={{
      current: listConst.DISPLAY_LIST
    }}
  />

EventList.propTypes = {
  name: T.string.isRequired,
  autoload: T.bool,
  url: T.oneOfType([T.string, T.array]).isRequired,
  customDefinition: T.arrayOf(T.shape({
    // data list prop types
  }))
}

EventList.defaultProps = {
  autoload: true,
  customDefinition: []
}

export {
  EventList
}
