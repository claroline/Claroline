import React from 'react'

import {schemeCategory20c} from 'd3-scale'

import {trans} from '#/main/app/intl/translation'
import {ContentCounter} from '#/main/app/content/components/counter'
import {ContentHtml} from '#/main/app/content/components/html'

const ForumInfo = (props) =>
  <section className="resource-info">
    <h3 className="h2">{trans('resource_overview_info', {}, 'resource')}</h3>

    {props.forum.display.description &&
      <div className="panel panel-default">
        <ContentHtml className="panel-body">{props.forum.display.description}</ContentHtml>
      </div>
    }

    <div className="row">
      <ContentCounter
        icon="fa fa-user"
        label={trans('participants')}
        color={schemeCategory20c[1]}
        value={props.forum.meta.users}
      />

      <ContentCounter
        icon="fa fa-comments"
        label={trans('subjects', {}, 'forum')}
        color={schemeCategory20c[5]}
        value={props.forum.meta.subjects}
      />

      <ContentCounter
        icon="fa fa-comment"
        label={trans('messages', {}, 'forum')}
        color={schemeCategory20c[9]}
        value={props.forum.meta.messages}
      />
    </div>
  </section>

export {
  ForumInfo
}
