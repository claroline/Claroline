import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {Event as EventTypes} from '#/plugin/cursus/prop-types'
import {EventCard} from '#/plugin/cursus/event/components/card'

const EventDisplay = (props) => props.data ?
  <EventCard
    data={props.data}
    size="xs"
  /> :
  <ContentPlaceholder
    icon="fa fa-calendar-day"
    title={trans('no_event', {}, 'cursus')}
  />

EventDisplay.propTypes = {
  data: T.shape(
    EventTypes.propTypes
  )
}

export {
  EventDisplay
}
