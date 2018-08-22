import React from 'react'

import {getPlainText} from '#/main/app/data/html/utils'
import {DataCard} from '#/main/core/data/components/data-card'

const AnnouncementCard = (props) =>
  <DataCard
    {...props}
    id={props.data.id}
    title={props.data.title}
    contentText={getPlainText(props.data.content)}
  />

export {
  AnnouncementCard
}
