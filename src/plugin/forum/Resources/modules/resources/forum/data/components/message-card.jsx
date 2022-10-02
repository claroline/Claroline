import React from 'react'

import {asset} from '#/main/app/config/asset'
import {getPlainText} from '#/main/app/data/types/html/utils'
import {DataCard} from '#/main/app/data/components/card'
import {UserAvatar} from '#/main/core/user/components/avatar'

const MessageCard = (props) =>
  <DataCard
    {...props}
    id={props.data.id}
    icon={<UserAvatar picture={props.data.meta.creator ? props.data.meta.creator.picture : undefined} alt={true}/>}
    poster={props.data.subject.poster ? asset(props.data.subject.poster) : null}
    title={props.data.subject.title}
    contentText={getPlainText(props.data.content)}
  />

export {
  MessageCard
}
