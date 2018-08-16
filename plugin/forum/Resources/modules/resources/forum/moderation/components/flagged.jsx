import React from 'react'

import {Routes} from '#/main/app/router'
import {trans} from '#/main/core/translation'

import {FlaggedMessages} from '#/plugin/forum/resources/forum/moderation/components/flagged-messages'
import {FlaggedSubjects} from '#/plugin/forum/resources/forum/moderation/components/flagged-subjects'
import {FlaggedPostsNav} from '#/plugin/forum/resources/forum/moderation/components/flagged-posts-nav'

const Flagged = () =>
  <div>
    <h2>{trans('flagged_messages_subjects', {}, 'forum')}</h2>
    <div className="row">
      <div className="col-md-3">
        <FlaggedPostsNav />
      </div>
      <div className="col-md-9">
        <Routes
          routes={[
            {
              path: '/moderation/flagged/subjects',
              component: FlaggedSubjects
            }, {
              path: '/moderation/flagged/messages',
              component: FlaggedMessages
            }
          ]}
        />
      </div>
    </div>
  </div>

export {
  Flagged
}
