import React from 'react'

import {trans} from '#/main/core/translation'
import {MetricCard} from '#/main/core/layout/components/metric-card'

const ForumInfo = (props) =>
  <section className="resource-info row">
    <div className="col-md-4">
      <MetricCard
        value={props.forum.meta.users}
        cardTitle={trans('participating_users', {}, 'forum')}
      />
    </div>
    <div className="col-md-4">
      <MetricCard
        value={props.forum.meta.subjects}
        cardTitle={trans('subjects', {}, 'forum')}
      />
    </div>
    <div className="col-md-4">
      <MetricCard
        value={props.forum.meta.messages}
        cardTitle={trans('messages', {}, 'forum')}
      />
    </div>
  </section>

export {
  ForumInfo
}
