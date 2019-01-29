import React from 'react'

import {getPlainText} from '#/main/app/data/types/html/utils'
import {DataCard} from '#/main/app/content/card/components/data'

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
