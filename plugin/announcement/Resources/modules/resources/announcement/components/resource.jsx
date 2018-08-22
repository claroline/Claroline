import React from 'react'
import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'

import {trans} from '#/main/core/translation'
import {makeId} from '#/main/core/scaffolding/id'

import {ResourcePage} from '#/main/core/resource/containers/page'
import {LINK_BUTTON} from '#/main/app/buttons'
import {RoutedPageContent} from '#/main/core/layout/router'

import {Announcement as AnnouncementTypes} from '#/plugin/announcement/resources/announcement/prop-types'
import {Announces} from '#/plugin/announcement/resources/announcement/components/announces'
import {Announce} from '#/plugin/announcement/resources/announcement/components/announce'
import {AnnounceForm} from '#/plugin/announcement/resources/announcement/components/announce-form'
import {AnnounceSend} from '#/plugin/announcement/resources/announcement/components/announce-send'

const AnnouncementResource = props =>
  <ResourcePage
    styles={['claroline-distribution-plugin-announcement-announcement-resource']}
    primaryAction="create-announce"
    customActions={[
      {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-list',
        label: trans('announcements_list', {}, 'announcement'),
        target: '/',
        exact: true
      }
    ]}
  >
    <RoutedPageContent
      routes={[
        {
          path: '/',
          exact: true,
          component: Announces
        }, {
          path: '/add',
          exact: true,
          component: AnnounceForm,
          onEnter: () => props.resetForm(merge({}, AnnouncementTypes.defaultProps, {
            id: makeId()
          }), true)
        }, {
          path: '/:id',
          component: Announce,
          exact: true,
          onEnter: (params) => props.openDetail(params.id),
          onLeave: props.resetDetail
        }, {
          path: '/:id/edit',
          component: AnnounceForm,
          onEnter: (params) => props.resetForm(props.posts.find(post => post.id === params.id))
        }, {
          path: '/:id/send',
          component: AnnounceSend,
          onEnter: (params) => {
            props.resetForm(props.posts.find(post => post.id === params.id))
            props.initFormDefaultRoles(props.roles.map(r => r.id))
          }
        }
      ]}
    />
  </ResourcePage>

AnnouncementResource.propTypes = {
  aggregateId: T.string.isRequired,
  posts: T.arrayOf(
    T.shape(AnnouncementTypes.propTypes)
  ).isRequired,
  roles: T.arrayOf(T.shape({
    id: T.string.isRequired
  })),
  openDetail: T.func.isRequired,
  resetDetail: T.func.isRequired,
  resetForm: T.func.isRequired,
  initFormDefaultRoles: T.func.isRequired
}

export {
  AnnouncementResource
}
