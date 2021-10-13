import React from 'react'
import get from 'lodash/get'

import {displayDate, trans} from '#/main/app/intl'
import {asset} from '#/main/app/config'
import {getPlainText} from '#/main/app/data/types/html/utils'

import {DataCard} from '#/main/app/data/components/card'
import {UserAvatar} from '#/main/core/user/components/avatar'
import {UserMicro} from '#/main/core/user/components/micro'

const PostCard = (props) =>
  <DataCard
    {...props}
    id={props.data.id}
    poster={props.data.thumbnail ? asset(props.data.thumbnail.url) : null}
    icon={
      get(props.data, 'meta.author') ?
        <UserAvatar alt={true} /> :
        <UserAvatar picture={get(props.data, 'meta.creator.picture')} alt={true} />
    }
    title={props.data.title}
    contentText={getPlainText(props.data.content)}
    footer={
      <span
        style={{
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'space-between'
        }}
      >
        {props.data.meta.author ?
          <UserMicro name={props.data.meta.author} /> :
          <UserMicro {...props.data.meta.creator} />
        }

        {props.data.publicationDate ?
          trans('published_at', {date: displayDate(props.data.publicationDate, false, true)}) : trans('not_published')
        }
      </span>
    }
  />

export {
  PostCard
}
