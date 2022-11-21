import React from 'react'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {asset} from '#/main/app/config/asset'
import {getPlainText} from '#/main/app/data/types/html/utils'
import {displayDate} from '#/main/app/intl/date'
import {DataCard} from '#/main/app/data/components/card'
import {UserMicro} from '#/main/core/user/components/micro'
import {ResourceIcon} from '#/main/core/resource/components/icon'

const AnnouncementCard = (props) =>
  <DataCard
    {...props}
    id={props.data.id}
    className={classes(props.className, {
      'data-card-muted': !props.data.meta.publishedAt
    })}
    icon={<ResourceIcon mimeType="custom/claroline_announcement_aggregate" />}
    title={props.data.title}
    poster={props.data.poster ? asset(props.data.poster) : null}
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

        {props.data.meta.publishedAt ?
          trans('published_at', {date: displayDate(props.data.meta.publishedAt, false, true)}) : trans('not_published')
        }
      </span>
    }
  />

export {
  AnnouncementCard
}
