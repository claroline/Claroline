import React from 'react'

import {asset} from '#/main/app/config/asset'
import {getPlainText} from '#/main/app/data/types/html/utils'
import {DataCard} from '#/main/app/content/card/components/data'

const EventCard = (props) =>
  <DataCard
    {...props}
    id={props.data.id}
    title={props.data.title}
    poster={props.data.thumbnail ? asset(props.data.thumbnail.url) : null}
    contentText={getPlainText(props.data.description)}
  />

export {
  EventCard
}
