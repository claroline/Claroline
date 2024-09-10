import React, {useEffect} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {actions as listActions} from '#/main/app/content/list/store'
import {ContentTags} from '#/main/app/content/components/tags'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {Forum as ForumType} from '#/plugin/forum/resources/forum/prop-types'
import {actions, selectors} from '#/plugin/forum/resources/forum/store'
import {LastMessages} from '#/plugin/forum/resources/forum/overview/components/last-messages'
import {ResourceOverview} from '#/main/core/resource'
import {PageSection} from '#/main/app/page/components/section'
import {ContentInfoBlocks} from '#/main/app/content/components/info-block'

const OverviewComponent = props => {
  useEffect(() => {
    props.loadLastMessages(props.forum)
  }, [get(props.forum, 'id')])

  return (
    <ResourceOverview
      primaryAction="create-subject"
      actions={[
        {
          name: 'create-subject',
          label: trans('create_subject', {}, 'forum'),
          type: LINK_BUTTON,
          target: `${props.path}/subjects/form`,
          displayed: !props.bannedUser
        }
      ]}
    >
      <PageSection size="md">
        <ContentInfoBlocks
          className="my-4"
          size="lg"
          items={[
            {
              icon: 'fa fa-user',
              label: trans('participants'),
              value: props.usersCount
            }, {
              icon: 'fa fa-comments',
              label: trans('subjects', {}, 'forum'),
              value: props.subjectsCount
            }, {
              icon: 'fa fa-comment',
              label: trans('messages', {}, 'forum'),
              value: props.messagesCount
            }
          ]}
        />
      </PageSection>

      {0 !== props.lastMessages.length &&
        <LastMessages
          lastMessages={props.lastMessages}
          path={props.path}
        />
      }

      {!isEmpty(props.tags) &&
        <PageSection size="md" title={trans('tags')}>
          <ContentTags
            className="text-center"
            tags={props.tagsCount}
            minSize={12}
            maxSize={28}
            onClick={(tag) => {
              const forumTag = props.tags.find(t => t.name === tag)

              if (forumTag) {
                props.goToList(forumTag.id)
                props.history.push(`${props.path}/subjects`)
              }
            }}
          />
        </PageSection>
      }
    </ResourceOverview>
  )
}

OverviewComponent.propTypes = {
  path: T.string.isRequired,
  forum: T.shape(ForumType.propTypes),
  lastMessages: T.array.isRequired,
  bannedUser: T.bool.isRequired,
  tags: T.array,
  tagsCount: T.object,
  usersCount: T.number.isRequired,
  subjectsCount: T.number.isRequired,
  messagesCount: T.number.isRequired,
  myMessages: T.number.isRequired,
  goToList: T.func.isRequired,
  history: T.shape({
    push: T.func.isRequired
  }).isRequired
}

OverviewComponent.defaultProps = {
  bannedUser: true,
  lastMessages: []
}

const Overview = connect(
  (state) => ({
    path: resourceSelectors.path(state),
    subject: selectors.subject(state),
    forum: selectors.forum(state),
    lastMessages: selectors.lastMessages(state).data,
    tags: selectors.tags(state),
    tagsCount: selectors.tagsCount(state),
    usersCount: selectors.usersCount(state),
    subjectsCount: selectors.subjectsCount(state),
    messagesCount: selectors.messagesCount(state),
    bannedUser: selectors.bannedUser(state),
    moderator: selectors.moderator(state),
    myMessages: selectors.myMessages(state)
  }),
  dispatch =>({
    loadLastMessages(forum) {
      dispatch(actions.fetchLastMessages(forum))
    },
    goToList(tag) {
      dispatch(listActions.addFilter(`${selectors.STORE_NAME}.subjects.list`, 'tags', tag))
      dispatch(listActions.invalidateData(`${selectors.STORE_NAME}.subjects.list`))
    }
  })
)(OverviewComponent)


export {
  Overview
}
