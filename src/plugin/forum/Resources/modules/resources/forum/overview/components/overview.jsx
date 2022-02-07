import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {number} from '#/main/app/intl'
import {Button} from '#/main/app/action/components/button'
import {LINK_BUTTON} from '#/main/app/buttons'
import {actions as listActions} from '#/main/app/content/list/store'
import {ContentTags} from '#/main/app/content/components/tags'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {CountGauge} from '#/main/core/layout/gauge/components/count-gauge'

import {Forum as ForumType} from '#/plugin/forum/resources/forum/prop-types'
import {selectors} from '#/plugin/forum/resources/forum/store'
import {LastMessages} from '#/plugin/forum/resources/forum/overview/components/last-messages'
import {ForumInfo} from '#/plugin/forum/resources/forum/overview/components/forum-info'

const OverviewComponent = props =>
  <section className="resource-section resource-overview">
    <h2 className="sr-only">{trans('resource_overview')}</h2>
    <div className="row">
      <div className="user-column col-md-4">
        <section className="user-progression">
          <h3 className="h2">{trans('my_participation', {}, 'forum')}</h3>
          <div className="panel panel-default">
            <div className="panel-body text-center">
              <CountGauge
                type="user"
                value={props.myMessages}
                displayValue={(value) => number(value, true)}
                width={140}
                height={140}
              />

              <h4 className="user-progression-status h5">
                {trans('my_messages', {}, 'forum')}
              </h4>
            </div>
          </div>
        </section>

        <section className="user-actions">
          <h3 className="sr-only">{trans('resource_overview_actions', {}, 'resource')}</h3>
          <Button
            label={trans('see_subjects', {}, 'forum')}
            type={LINK_BUTTON}
            target={`${props.path}/subjects`}
            className="btn btn-block btn-emphasis"
            primary={true}
          />

          {!props.bannedUser &&
            <Button
              label={trans('create_subject', {}, 'forum')}
              type={LINK_BUTTON}
              target={`${props.path}/subjects/form`}
              className="btn btn-block"
            />
          }
        </section>

        {!isEmpty(props.forum.meta.tags) &&
          <section>
            <h3 className="h2">{trans('tags')}</h3>
            <ContentTags
              tags={props.tagsCount}
              minSize={12}
              maxSize={28}
              onClick={(tag) => {
                const forumTag = props.forum.meta.tags.find(t => t.name === tag)

                if (forumTag) {
                  props.goToList(forumTag.id)
                  props.history.push(`${props.path}/subjects`)
                }
              }}
            />
          </section>
        }
      </div>

      <div className="resource-column col-md-8">
        <ForumInfo
          forum={props.forum}
        />

        {0 !== props.lastMessages.length &&
          <LastMessages
            lastMessages={props.lastMessages}
            path={props.path}
          />
        }
      </div>
    </div>
  </section>

OverviewComponent.propTypes = {
  path: T.string.isRequired,
  forum: T.shape(ForumType.propTypes),
  lastMessages: T.array.isRequired,
  bannedUser: T.bool.isRequired,
  tagsCount: T.shape({}),
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
    tagsCount: selectors.tagsCount(state),
    bannedUser: selectors.bannedUser(state),
    moderator: selectors.moderator(state),
    myMessages: selectors.myMessages(state)
  }),
  dispatch =>({
    goToList(tag) {
      dispatch(listActions.addFilter(`${selectors.STORE_NAME}.subjects.list`, 'tags', tag))
      dispatch(listActions.invalidateData(`${selectors.STORE_NAME}.subjects.list`))
    }
  })
)(OverviewComponent)


export {
  Overview
}
