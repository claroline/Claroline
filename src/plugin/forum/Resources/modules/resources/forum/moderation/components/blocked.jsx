import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {NavLink, Routes} from '#/main/app/router'

import {BlockedMessages} from '#/plugin/forum/resources/forum/moderation/components/blocked-messages'
import {BlockedSubjects} from '#/plugin/forum/resources/forum/moderation/components/blocked-subjects'
import {ResourcePage} from '#/main/core/resource'

const Blocked = (props) =>
  <ResourcePage
    title={trans('blocked_messages_subjects', {}, 'forum')}
  >
    <div className="row">
      <div className="col-md-3">
        <nav className="lateral-nav">
          <NavLink
            to={`${props.path}/moderation/blocked/subjects`}
            className="lateral-link"
          >
            {trans('blocked_subjects', {}, 'forum')}
          </NavLink>

          <NavLink
            to={`${props.path}/moderation/blocked/messages`}
            className="lateral-link"
          >
            {trans('blocked_messages', {}, 'forum')}
          </NavLink>
        </nav>
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
  </ResourcePage>

Blocked.propTypes = {
  path: T.string.isRequired
}

export {
  Blocked
}
