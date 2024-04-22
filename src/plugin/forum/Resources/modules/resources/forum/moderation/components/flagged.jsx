import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {NavLink, Routes} from '#/main/app/router'

import {FlaggedMessages} from '#/plugin/forum/resources/forum/moderation/components/flagged-messages'
import {FlaggedSubjects} from '#/plugin/forum/resources/forum/moderation/components/flagged-subjects'
import {ResourcePage} from '#/main/core/resource'

const Flagged = (props) =>
  <ResourcePage
    title={trans('flagged_messages_subjects', {}, 'forum')}
  >
    <div className="row">
      <div className="col-md-3">
        <nav className="lateral-nav">
          <NavLink
            to={`${props.path}/moderation/flagged/subjects`}
            className="lateral-link"
          >
            {trans('flagged_subjects', {}, 'forum')}
          </NavLink>

          <NavLink
            to={`${props.path}/moderation/flagged/messages`}
            className="lateral-link"
          >
            {trans('flagged_messages', {}, 'forum')}
          </NavLink>
        </nav>
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
  </ResourcePage>

Flagged.propTypes = {
  path: T.string.isRequired
}

export {
  Flagged
}
