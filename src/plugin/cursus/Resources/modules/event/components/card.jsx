import React from 'react'
import {PropTypes as T} from 'prop-types'
import moment from 'moment'
import get from 'lodash/get'

import {asset} from '#/main/app/config/asset'
import {trans, displayDate} from '#/main/app/intl'
import {DataCard} from '#/main/app/data/components/card'
import {UserMicroList} from '#/main/core/user/components/micro-list'

import {Event as EventTypes} from '#/plugin/cursus/prop-types'

const EventCard = props => {
  let date = displayDate(props.data.start, true)
  if (props.data.end) {
    if (moment(props.data.start).isSame(props.data.end, 'day')) {
      date += ' | ' + trans('time_range', {
        start: moment(props.data.start).format('LT'),
        end: moment(props.data.end).format('LT')
      })
    } else {
      date = trans('date_range', {
        start: displayDate(props.data.start, true),
        end: displayDate(props.data.end, true)
      })
    }
  }

  return (
    <DataCard
      {...props}
      id={props.data.id}
      poster={props.data.thumbnail ? asset(props.data.thumbnail.url) : null}
      icon="fa fa-clock-o"
      title={props.data.name}
      subtitle={date}
      contentText={props.data.description}
      footer={
        <span
          style={{
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'space-between'
          }}
        >
          {0 !== props.data.tutors.length &&
            <UserMicroList
              id={`event-tutors-${props.data.id}`}
              label={trans('tutors', {}, 'cursus')}
              users={props.data.tutors}
            />
          }

          {get(props.data, 'location.name') || trans('online_session', {}, 'cursus')}
        </span>
      }
    />
  )
}

EventCard.propTypes = {
  data: T.shape(
    EventTypes.propTypes
  ).isRequired
}

export {
  EventCard
}
