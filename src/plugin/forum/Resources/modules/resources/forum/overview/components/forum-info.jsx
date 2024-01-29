import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {ContentInfoBlocks} from '#/main/app/content/components/info-block'
import {ContentHtml} from '#/main/app/content/components/html'

const ForumInfo = (props) =>
  <section className="resource-info">
    <h3 className="h2">{trans('resource_overview_info', {}, 'resource')}</h3>

    {props.forum.display.description &&
      <div className="card mb-3">
        <ContentHtml className="card-body">{props.forum.display.description}</ContentHtml>
      </div>
    }

    <ContentInfoBlocks
      className="my-4"
      size="lg"
      items={[
        {
          icon: 'fa fa-user',
          label: trans('participants'),
          value: props.forum.meta.users
        }, {
          icon: 'fa fa-comments',
          label: trans('subjects', {}, 'forum'),
          value: props.forum.meta.subjects
        }, {
          icon: 'fa fa-comment',
          label: trans('messages', {}, 'forum'),
          value: props.forum.meta.messages
        }
      ]}
    />
  </section>

export {
  ForumInfo
}
