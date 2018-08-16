import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/core/translation'
import {RoutedPageContent} from '#/main/core/layout/router'
import {ResourcePage} from '#/main/core/resource/containers/page'
import {currentUser} from '#/main/core/user/current'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'

import {Overview} from '#/plugin/forum/resources/forum/overview/components/overview'
import {Moderation} from '#/plugin/forum/resources/forum/moderation/components/moderation'
import {Editor} from '#/plugin/forum/resources/forum/editor/components/editor'
import {Player} from '#/plugin/forum/resources/forum/player/components/player'
import {Forum as ForumType} from '#/plugin/forum/resources/forum/prop-types'

const ForumResource = props =>
  <ResourcePage
    styles={['claroline-distribution-plugin-forum-forum-resource']}
    primaryAction="post"
    customActions={[
      {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-home',
        label: trans('show_overview'),
        displayed: !!get(props.forum, 'display.showOverview'),
        target: '/',
        exact: true
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-list-ul',
        label: trans('see_subjects', {}, 'forum'),
        target: '/subjects',
        exact: true
      }, {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-envelope',
        label: trans('receive_notifications', {}, 'forum'),
        displayed: !get(props.forum, 'meta.notified'),
        callback: () => props.notify(props.forum, currentUser())
      },{
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-envelope-o',
        label: trans('stop_receive_notifications', {}, 'forum'),
        displayed: !!get(props.forum, 'meta.notified'),
        callback: () => props.stopNotify(props.forum, currentUser())
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-gavel',
        label: trans('moderated_posts', {}, 'forum'),
        group: trans('moderation', {}, 'forum'),
        displayed: !!get(props.forum, 'restrictions.moderator'),
        target: '/moderation/blocked',
        exact: true
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-flag',
        label: trans('flagged_messages_subjects', {}, 'forum'),
        group: trans('moderation', {}, 'forum'),
        displayed: !!get(props.forum, 'restrictions.moderator'),
        target: '/moderation/flagged/subjects',
        exact: true
      }
    ]}
  >
    <RoutedPageContent
      headerSpacer={false}
      routes={[
        {
          path: '/edit',
          component: Editor,
          disabled: !props.editable
        }, {
          path: '/',
          exact: true,
          component: Overview,
          onEnter: () => props.loadLastMessages(props.forum),
          disabled: !get(props.forum, 'display.showOverview')
        }, {
          path: '/subjects',
          component: Player
        },  {
          path: '/moderation',
          component: Moderation
        }
      ]}
      redirect={[
        {
          disabled: !!get(props.forum, 'display.showOverview'),
          from: '/',
          to: '/subjects',
          exact: true
        }
      ]}
    />
  </ResourcePage>

ForumResource.propTypes = {
  forum: T.shape(ForumType.propTypes).isRequired,
  editable: T.bool.isRequired,
  loadLastMessages: T.func.isRequired,
  notify: T.func.isRequired,
  stopNotify: T.func.isRequired
}

export {
  ForumResource
}
