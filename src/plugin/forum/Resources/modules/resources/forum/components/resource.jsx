import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {Resource} from '#/main/core/resource'

import {Overview} from '#/plugin/forum/resources/forum/overview/components/overview'
import {Moderation} from '#/plugin/forum/resources/forum/moderation/components/moderation'
import {Player} from '#/plugin/forum/resources/forum/player/components/player'
import {Forum as ForumType} from '#/plugin/forum/resources/forum/prop-types'
import {constants} from '#/plugin/forum/resources/forum/constants'
import {ForumEditor} from '#/plugin/forum/resources/forum/editor/components/main'

const ForumResource = props =>
  <Resource
    {...omit(props)}
    styles={['claroline-distribution-plugin-forum-forum-resource']}
    menu={[
      {
        name: 'subjects',
        type: LINK_BUTTON,
        //icon: 'fa fa-fw fa-list-ul',
        label: trans('subjects', {}, 'forum'),
        target: `${props.path}/subjects`
      }, {
        name: 'moderation',
        type: LINK_BUTTON,
        //icon: 'fa fa-fw fa-flag',
        label: trans('moderation', {}, 'forum'),
        displayed: props.moderator,
        target: `${props.path}/moderation`
      }
    ]}
    actions={[
      {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-bell',
        label: trans('receive_notifications', {}, 'forum'),
        displayed: !props.notified,
        callback: () => props.notify(props.forum, props.currentUser)
      }, {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-bell',
        label: trans('stop_receive_notifications', {}, 'forum'),
        displayed: props.notified,
        callback: () => props.stopNotify(props.forum, props.currentUser)
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-gavel',
        label: trans('blocked_messages_subjects', {}, 'forum'),
        group: trans('moderation', {}, 'forum'),
        displayed: constants.VALIDATE_NONE !== get(props.forum, 'moderation') && props.moderator,
        target: `${props.path}/moderation/blocked/subjects`
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-flag',
        label: trans('flagged_messages_subjects', {}, 'forum'),
        group: trans('moderation', {}, 'forum'),
        displayed: props.moderator,
        target: `${props.path}/moderation/flagged/subjects`
      }
    ]}
    editor={ForumEditor}
    overviewPage={Overview}
    pages={[
      {
        path: '/subjects',
        component: Player
      }, {
        path: '/moderation',
        disabled: !props.moderator,
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
  moderator: T.bool.isRequired,
  editable: T.bool.isRequired,
  loadLastMessages: T.func.isRequired,
  notified: T.bool.isRequired,
  notify: T.func.isRequired,
  stopNotify: T.func.isRequired
}

export {
  ForumResource
}
