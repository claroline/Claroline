import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {asset} from '#/main/app/config/asset'
import {trans, displayDateRange} from '#/main/app/intl'
import {DataCard} from '#/main/app/data/components/card'
import {UserMicroList} from '#/main/core/user/components/micro-list'

import {Event as EventTypes} from '#/plugin/cursus/prop-types'

const EventCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    poster={props.data.thumbnail ? asset(props.data.thumbnail.url) : null}
    icon="fa fa-clock-o"
    title={props.data.name}
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
        {0 !== props.data.tutors.length &&
          <UserMicroList
            id={`event-tutors-${props.data.id}`}
            label={trans('tutors', {}, 'cursus')}
            users={props.data.tutors}
          />
        }

        {get(props.data, 'location.name') || get(props.data, 'locationUrl') ? <a href={get(props.data, 'locationUrl')}>{get(props.data, 'locationUrl')}</a> : trans('online_session', {}, 'cursus')}
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
