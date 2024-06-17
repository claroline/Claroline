import React, {useEffect} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {number} from '#/main/app/intl'
import {Button} from '#/main/app/action/components/button'
import {LINK_BUTTON} from '#/main/app/buttons'
import {actions as listActions} from '#/main/app/content/list/store'
import {ContentTags} from '#/main/app/content/components/tags'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {CountGauge} from '#/main/core/layout/gauge/components/count-gauge'

import {Forum as ForumType} from '#/plugin/forum/resources/forum/prop-types'
import {actions, selectors} from '#/plugin/forum/resources/forum/store'
import {LastMessages} from '#/plugin/forum/resources/forum/overview/components/last-messages'
import {ForumInfo} from '#/plugin/forum/resources/forum/overview/components/forum-info'
import {ResourcePage} from '#/main/core/resource'

const OverviewComponent = props => {
  useEffect(() => {
    props.loadLastMessages(props.forum)
  }, [get(props.forum, 'id')])

  return (
    <ResourcePage>
      <section className="resource-section resource-overview content-lg mt-3">
        <h2 className="sr-only">{trans('resource_overview', {}, 'resource')}</h2>
        <div className="row">
          <div className="user-column col-md-4">
            <section className="user-progression">
              <h3 className="h2">{trans('my_participation', {}, 'forum')}</h3>
              <div className="card">
                <div className="card-body text-center">
                  <CountGauge
                    type="user"
                    className="mb-3"
                    value={props.myMessages}
                    displayValue={(value) => number(value, true)}
                    width={140}
                    height={140}
                  />

                  <div className="user-progression-status lead">
                    {trans('my_messages', {}, 'forum')}
                  </div>
                </div>
              </div>
            </section>

            <section className="user-actions">
              <h3 className="sr-only">{trans('resource_overview_actions', {}, 'resource')}</h3>
              <Button
                label={trans('see_subjects', {}, 'forum')}
                type={LINK_BUTTON}
                target={`${props.path}/subjects`}
                className="btn btn-primary w-100"
                primary={true}
                size="lg"
              />

              {!props.bannedUser &&
                <Button
                  label={trans('create_subject', {}, 'forum')}
                  type={LINK_BUTTON}
                  target={`${props.path}/subjects/form`}
                  className="btn btn-outline-primary w-100 mt-1"
                />
              }
            </section>

            {!isEmpty(props.forum.meta.tags) &&
              <section className="mt-3">
                <h3 className="h2">{trans('tags')}</h3>
                <div className="card">
                  <div className="card-body text-center">
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
                  </div>
                </div>
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
    </ResourcePage>
  )
}

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
