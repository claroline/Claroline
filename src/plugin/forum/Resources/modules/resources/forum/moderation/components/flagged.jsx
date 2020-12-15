import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'

import {FlaggedMessages} from '#/plugin/forum/resources/forum/moderation/components/flagged-messages'
import {FlaggedSubjects} from '#/plugin/forum/resources/forum/moderation/components/flagged-subjects'
import {FlaggedPostsNav} from '#/plugin/forum/resources/forum/moderation/components/flagged-posts-nav'

const Flagged = (props) =>
  <div>
    <h2>{trans('flagged_messages_subjects', {}, 'forum')}</h2>
    <div className="row">
      <div className="col-md-3">
        <FlaggedPostsNav path={props.path} />
      </div>
      <div className="col-md-9">
        <Routes
          path={props.path}
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

Flagged.propTypes = {
  path: T.string.isRequired
}

export {
  Flagged
}
