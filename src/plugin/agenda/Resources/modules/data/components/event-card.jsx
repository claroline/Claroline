import React from 'react'
import {PropTypes as T} from 'prop-types'

import {displayDate} from '#/main/app/intl/date'
import {asset} from '#/main/app/config/asset'
import {getPlainText} from '#/main/app/data/types/html/utils'
import {DataCard} from '#/main/app/data/components/card'

import {constants} from '#/plugin/agenda/event/constants'
import {Event as EventTypes} from '#/plugin/agenda/event/prop-types'

const EventCard = (props) =>
  <DataCard
    {...props}
    id={props.data.id}
    title={props.data.title}
    subtitle={displayDate(props.data.start, false, true) + (constants.EVENT_TYPE_EVENT === props.data.meta.type && props.data.end ? ' / ' + displayDate(props.data.end, false, true) : '')}
    poster={props.data.thumbnail ? asset(props.data.thumbnail.url) : null}
    contentText={getPlainText(props.data.description)}
  />

EventCard.propTypes = {
  data: T.shape(
    EventTypes.propTypes
  ).isRequired
}

export {
  EventCard
}
