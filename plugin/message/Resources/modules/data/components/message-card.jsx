import React from 'react'

import {getPlainText} from '#/main/app/data/html/utils'
import {DataCard} from '#/main/core/data/components/data-card'
import {UserAvatar} from '#/main/core/user/components/avatar'

const MessageCard = (props) =>
  <DataCard
    {...props}
    id={props.data.id}
    icon={<UserAvatar picture={props.data.from ? props.data.from.picture : undefined} alt={true}/>}
    title={props.data.object}
    contentText={getPlainText(props.data.content)}
  />

export {
  MessageCard
}
