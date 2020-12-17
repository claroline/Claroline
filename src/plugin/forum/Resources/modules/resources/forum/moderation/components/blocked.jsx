import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'

import {BlockedMessages} from '#/plugin/forum/resources/forum/moderation/components/blocked-messages'
import {BlockedSubjects} from '#/plugin/forum/resources/forum/moderation/components/blocked-subjects'
import {BlockedPostsNav} from '#/plugin/forum/resources/forum/moderation/components/blocked-posts-nav'

const Blocked = (props) =>
  <div>
    <h2>{trans('blocked_messages_subjects', {}, 'forum')}</h2>
    <div className="row">
      <div className="col-md-3">
        <BlockedPostsNav path={props.path} />
      </div>
      <div className="col-md-9">
        <Routes
          path={props.path}
          routes={[
            {
              path: '/moderation/blocked/subjects',
              component: BlockedSubjects
            }, {
              path: '/moderation/blocked/messages',
              component: BlockedMessages
            }
          ]}
        />
      </div>
    </div>
  </div>

Blocked.propTypes = {
  path: T.string.isRequired
}

export {
  Blocked
}
