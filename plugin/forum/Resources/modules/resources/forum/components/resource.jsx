import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {selectors as resourceSelect} from '#/main/core/resource/store/selectors'
import {hasPermission} from '#/main/core/resource/permissions'
import {actions as formActions} from '#/main/app/content/form/store/actions'
import {RoutedPageContent} from '#/main/core/layout/router'
import {ResourcePageContainer} from '#/main/core/resource/containers/page'
import {currentUser} from '#/main/core/user/current'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'

import {Forum as ForumType} from '#/plugin/forum/resources/forum/prop-types'
import {select} from '#/plugin/forum/resources/forum/selectors'
import {actions} from '#/plugin/forum/resources/forum/actions'
import {Overview} from '#/plugin/forum/resources/forum/overview/components/overview'
import {Moderation} from '#/plugin/forum/resources/forum/moderation/components/moderation'
import {Editor} from '#/plugin/forum/resources/forum/editor/components/editor'
import {Player} from '#/plugin/forum/resources/forum/player/components/player'

const Resource = props => {
  const redirect = []
  const routes = [
    {
      path: '/edit',
      component: Editor,
      disabled: !props.editable,
      onEnter: () => props.resetForm(props.forum)
    }, {
      path: '/',
      exact: true,
      component: Overview,
      onEnter: () => props.loadLastMessages(props.forum),
      disabled: !props.forum.display.showOverview
    }, {
      path: '/subjects',
      component: Player
    },  {
      path: '/moderation',
      component: Moderation
    }
  ]
  if (!props.forum.display.showOverview) {
    // redirect to player
    redirect.push({
      from: '/',
      to: '/subjects',
      exact: true
    })
  }

  return (
    <ResourcePageContainer
      primaryAction="post"
      customActions={[
        {
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-home',
          label: trans('show_overview'),
          displayed: props.forum.display.showOverview,
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
          displayed: !props.forum.meta.notified,
          callback: () => props.notify(props.forum, currentUser())
        },{
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-envelope-o',
          label: trans('stop_receive_notifications', {}, 'forum'),
          displayed: props.forum.meta.notified,
          callback: () => props.stopNotify(props.forum, currentUser())
        }, {
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-gavel',
          label: trans('moderated_posts', {}, 'forum'),
          group: trans('moderation', {}, 'forum'),
          displayed: props.moderator,
          target: '/moderation/blocked',
          exact: true
        }, {
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-flag',
          label: trans('flagged_messages_subjects', {}, 'forum'),
          group: trans('moderation', {}, 'forum'),
          displayed: props.moderator,
          target: '/moderation/flagged/subjects',
          exact: true
        }
      ]}
    >
      <RoutedPageContent
        headerSpacer={false}
        redirect={redirect}
        routes={routes}
      />
    </ResourcePageContainer>
  )
}

Resource.propTypes = {
  forum: T.shape(ForumType.propTypes).isRequired,
  editable: T.bool.isRequired,
  loadLastMessages: T.func.isRequired,
  moderator: T.bool.isRequired,
  notify: T.func.isRequired,
  stopNotify: T.func.isRequired,
  resetForm: T.func.isRequired
}

const ForumResource = connect(
  (state) => ({
    forum: select.forum(state),
    editable: hasPermission('edit', resourceSelect.resourceNode(state)),
    moderator: select.moderator(state)
  }),
  (dispatch) => ({
    loadLastMessages(forum) {
      dispatch(actions.fetchLastMessages(forum))
    },
    notify(forum, user) {
      dispatch(actions.notify(forum, user))
    },
    stopNotify(forum, user) {
      dispatch(actions.stopNotify(forum, user))
    },
    resetForm(formData) {
      dispatch(formActions.resetForm('forumForm', formData))
    }
  })
)(Resource)

export {
  ForumResource
}
