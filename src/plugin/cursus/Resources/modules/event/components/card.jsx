import React from 'react'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/app/config/asset'
import {trans, displayDate} from '#/main/app/intl'
import {DataCard} from '#/main/app/data/components/card'

import {Event as EventTypes} from '#/plugin/cursus/prop-types'

const EventCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    poster={props.data.thumbnail ? asset(props.data.thumbnail.url) : null}
    icon="fa fa-clock-o"
    title={props.data.name}
    subtitle={trans('date_range', {
      start: displayDate(props.data.start),
      end: displayDate(props.data.end)
    })}
    contentText={props.data.description}
  />

EventCard.propTypes = {
  data: T.shape(
    EventTypes.propTypes
  ).isRequired
}

export {
  EventCard
}
