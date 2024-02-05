import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {asset} from '#/main/app/config/asset'
import {trans, displayDateRange} from '#/main/app/intl'
import {DataCard} from '#/main/app/data/components/card'
import {UserMicroList} from '#/main/core/user/components/micro-list'

import {EventStatus} from '#/plugin/cursus/components/event-status'
import {Event as EventTypes} from '#/plugin/cursus/prop-types'

const EventCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    poster={props.data.thumbnail ? asset(props.data.thumbnail) : null}
    icon={!props.data.thumbnail ? 'fa fa-clock' : null}
    title={
      <>
        <EventStatus className="me-2" startDate={props.data.start} endDate={props.data.end} />

        {props.data.name}
      </>
    }
    subtitle={displayDateRange(props.data.start, props.data.end)}
    contentText={props.data.description}
    footer={
      <span
        style={{
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'space-between'
        }}
      >
        {props.data.tutors && 0 !== props.data.tutors.length &&
          <UserMicroList
            id={`event-tutors-${props.data.id}`}
            label={trans('tutors', {}, 'cursus')}
            users={props.data.tutors}
          />
        }

        <span className="event-location">
          {get(props.data, 'location.name') || (get(props.data, 'locationUrl') ? <a href={get(props.data, 'locationUrl')}>{get(props.data, 'locationUrl')}</a> : trans('online_session', {}, 'cursus'))}
        </span>
      </span>
    }
  />

EventCard.propTypes = {
  data: T.shape(
    EventTypes.propTypes
  ).isRequired
}

export {
  EventCard
}
