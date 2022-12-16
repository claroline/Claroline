import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ResourcePage} from '#/main/core/resource/containers/page'

import {Overview} from '#/plugin/forum/resources/forum/overview/components/overview'
import {Moderation} from '#/plugin/forum/resources/forum/moderation/components/moderation'
import {Editor} from '#/plugin/forum/resources/forum/editor/components/editor'
import {Player} from '#/plugin/forum/resources/forum/player/components/player'
import {Forum as ForumType} from '#/plugin/forum/resources/forum/prop-types'

const ForumResource = props =>
  <ResourcePage
    primaryAction="post"
    customActions={[
      {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-home',
        label: trans('show_overview'),
        displayed: !!get(props.forum, 'display.showOverview'),
        target: props.path,
        exact: true
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-list-ul',
        label: trans('see_subjects', {}, 'forum'),
        target: `${props.path}/subjects`,
        exact: true
      }, {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-envelope',
        label: trans('receive_notifications', {}, 'forum'),
        displayed: !get(props.forum, 'meta.notified'),
        callback: () => props.notify(props.forum, props.currentUser)
      }, {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-envelope-o',
        label: trans('stop_receive_notifications', {}, 'forum'),
        displayed: !!get(props.forum, 'meta.notified'),
        callback: () => props.stopNotify(props.forum, props.currentUser)
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-gavel',
        label: trans('blocked_messages_subjects', {}, 'forum'),
        group: trans('moderation', {}, 'forum'),
        displayed: !!get(props.forum, 'restrictions.moderator'),
        target: `${props.path}/moderation/blocked/subjects`,
        exact: true
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-flag',
        label: trans('flagged_messages_subjects', {}, 'forum'),
        group: trans('moderation', {}, 'forum'),
        displayed: !!get(props.forum, 'restrictions.moderator'),
        target: `${props.path}/moderation/flagged/subjects`,
        exact: true
      }
    ]}
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
      }, {
        path: '/moderation',
        disabled: !get(props.forum, 'restrictions.moderator', false),
        render: () => {
          const component = <Moderation path={props.path} />

          return component
        }
      }
    ]}
    redirect={[
      {from: '/', to: '/subjects', exact: true, disabled: !!get(props.forum, 'display.showOverview')}
    ]}
  />

ForumResource.propTypes = {
  path: T.string.isRequired,
  currentUser: T.object,
  forum: T.shape(ForumType.propTypes).isRequired,
  editable: T.bool.isRequired,
  loadLastMessages: T.func.isRequired,
  notify: T.func.isRequired,
  stopNotify: T.func.isRequired
}

export {
  ForumResource
}
