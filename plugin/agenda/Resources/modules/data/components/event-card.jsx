import React from 'react'

import {getPlainText} from '#/main/app/data/html/utils'
import {DataCard} from '#/main/core/data/components/data-card'

const EventCard = (props) =>
  <DataCard
    {...props}
    id={props.data.id}
    title={props.data.title}
    contentText={getPlainText(props.data.description)}
  />

export {
  EventCard
}
