import React from 'react'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {asset} from '#/main/app/config/asset'
import {getPlainText} from '#/main/app/data/html/utils'
import {displayDate} from '#/main/app/intl/date'
import {DataCard} from '#/main/core/data/components/data-card'
import {ResourceIcon} from '#/main/core/resource/components/icon'
import {UserMicro} from '#/main/core/user/components/micro'

// TODO : make footer generic

const AnnouncementCard = (props) =>
  <DataCard
    {...props}
    id={props.data.id}
    className={classes(props.className, {
      'data-card-muted': !props.data.meta.publishedAt
    })}
    icon={<ResourceIcon className="icon" mimeType="custom/claroline_announcement_aggregate" />}
    title={props.data.title}
    poster={props.data.poster ? asset(props.data.poster.url) : null}
    contentText={getPlainText(props.data.content)}
    footer={
      <span
        style={{
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'space-between'
        }}
      >
        {props.data.author ?
          <UserMicro name={props.meta.author} /> :
          <UserMicro {...props.meta.creator} />
        }

        {props.meta.publishedAt ?
          trans('published_at', {date: displayDate(props.meta.publishedAt, false, true)}) : trans('not_published')
        }
      </span>
    }
  />

export {
  AnnouncementCard
}
