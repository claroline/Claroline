import React, {createElement, useEffect, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl'
import {
  PageContent,
  PageHeading,
  PageHeadingSkeleton,
  PageSection,
  PageTabbedSection, PageToolbar,
  PageToolbarSkeleton
} from '#/main/app/page'
import {Action as ActionTypes, PromisedAction as PromisedActionTypes} from '#/main/app/action/prop-types'
import {DetailsData} from '#/main/app/content/details'
import {getProfile} from '#/main/community/user/utils'

import {User as UserTypes} from '#/main/community/user/prop-types'
import {UserAvatar} from '#/main/app/user/components/avatar'
import {UserGroups} from '#/main/community/user/components/groups'
import {UserAbout} from '#/main/community/user/components/about'
import {UserActivity} from '#/main/community/user/components/activity'
import {Html} from '#/main/app/components/html'
import {TextSkeleton} from '#/main/app/components/placeholder'

const UserProfile = (props) => {
  const [profilePages, setProfilePages] = useState([])

  const userDefinition = [
    {
      title: trans('general'),
      primary: true,
      fields: [
        {
          name: 'lastActivity',
          type: 'date',
          label: trans('last_activity'),
          options: {time: true, long: true}
        }, {
          name: 'meta.created',
          type: 'date',
          label: trans('registration_date'),
          options: {time: true, long: true}
        }, {
          name: 'email',
          type: 'email',
          label: trans('email'),
          required: true,
          options: {
            unique: {
              check: ['apiv2_user_get', {field: 'email'}]
            }
          }
        }, {
          name: 'phone',
          type: 'phone',
          label: trans('phone')
        }, {
          name: 'code',
          type: 'string',
          label: trans('code')
        }
      ]
    }
  ]

  useEffect(() => {
    getProfile().then(profilePages => setProfilePages(profilePages))
  }, [props.path])

  return (
    <>
      {isEmpty(props.user) &&
        <PageContent className="placeholder-glow">
          <PageToolbarSkeleton toolbar="edit send-message more" />
          <PageHeadingSkeleton
            icon={true}
          />

          <PageSection className="mb-5">
            <TextSkeleton className="mb-4" />

            <DetailsData
              loaded={false}
              definition={userDefinition}
            />
          </PageSection>
        </PageContent>
      }

      {!isEmpty(props.user) &&
        <PageContent poster={get(props.user, 'poster')}>
          <PageToolbar
            toolbar="edit send-message more"
            actions={props.actions}
          />
          <PageHeading
            icon={
              <UserAvatar user={props.user} size="lg" border={true} />
            }
            title={get(props.user, 'name')}
          />

          <PageSection className="mb-5">
            {get(props.user, 'meta.description') &&
              <Html className="content-text mb-4">{get(props.user, 'meta.description')}</Html>
            }

            <DetailsData
              data={props.user}
              definition={userDefinition}
            />
          </PageSection>

          <PageTabbedSection
            className="mt-3"
            tabs={[
              {
                name: 'activity',
                title: trans('activity'),
                render: () => (
                  <UserActivity user={props.user} />
                )
              }, {
                name: 'about',
                title: trans('about'),
                render: () => (
                  <UserAbout user={props.user} />
                )
              }, {
                name: 'groups',
                title: trans('groups', {}, 'community'),
                render: () => (
                  <UserGroups path={props.path} user={props.user} addGroups={props.addGroups} />
                )
              }
            ].concat(profilePages.map(profilePage => ({
              ...omit(profilePage, 'component'),
              render: () => {
                return createElement(profilePage.component, {
                  path: props.path,
                  user: props.user
                })
              }
            })))}
          />
        </PageContent>
      }
    </>
  )
}

UserProfile.propTypes = {
  path: T.string.isRequired,
  user: T.shape(
    UserTypes.propTypes
  ),
  primaryAction: T.string,
  actions: T.oneOfType([
    // a regular array of actions
    T.arrayOf(T.shape(
      ActionTypes.propTypes
    )),
    // a promise that will resolve a list of actions
    T.shape(
      PromisedActionTypes.propTypes
    )
  ]),
  addGroups: T.func.isRequired
}

export {
  UserProfile
}
