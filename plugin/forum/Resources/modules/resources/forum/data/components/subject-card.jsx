import React from 'react'

import {trans, transChoice} from '#/main/core/translation'
import {getPlainText} from '#/main/app/data/html/utils'
import {asset} from '#/main/core/scaffolding/asset'
import {DataCard} from '#/main/core/data/components/data-card'
import {UserAvatar} from '#/main/core/user/components/avatar'

const SubjectCard = (props) =>
  <DataCard
    {...props}
    id={props.data.id}
    icon={<UserAvatar picture={props.data.meta.creator ? props.data.meta.creator.picture : undefined} alt={true}/>}
    title={props.data.title}
    poster={props.data.poster ? asset(props.data.poster.url) : null}
    subtitle={transChoice('replies', props.data.meta.messages, {count: props.data.meta.messages}, 'forum')}
    flags={[
      props.data.meta.hot && ['fa fa-fw fa-fire', trans('hot_subject', {}, 'forum')],
      props.data.meta.sticky && ['fa fa-fw fa-thumb-tack', trans('stuck', {}, 'forum')],
      props.data.meta.closed && ['fa fa-fw fa-times-circle-o', trans('closed_subject', {}, 'forum')]
    ].filter(flag => !!flag)}
    contentText={getPlainText(props.contentText)}
  />

export {
  SubjectCard
}
