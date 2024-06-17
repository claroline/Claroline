import React from 'react'
import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {makeId} from '#/main/core/scaffolding/id'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Resource} from '#/main/core/resource'

import {Announcement as AnnouncementTypes} from '#/plugin/announcement/resources/announcement/prop-types'
import {Announces} from '#/plugin/announcement/resources/announcement/components/announces'
import {Announce} from '#/plugin/announcement/resources/announcement/components/announce'
import {AnnounceForm} from '#/plugin/announcement/resources/announcement/components/announce-form'
import {AnnouncementEditor} from '#/plugin/announcement/resources/announcement/components/editor'

const AnnouncementResource = props =>
  <Resource
    {...omit(props)}
    styles={['claroline-distribution-plugin-announcement-announcement-resource']}
    menu={[
      {
        name: 'all',
        type: LINK_BUTTON,
        //icon: 'fa fa-fw fa-list',
        label: trans('announcements_list', {}, 'announcement'),
        target: props.path+'/all'
      }
    ]}
    editor={AnnouncementEditor}
    pages={[
      {
        path: '/all',
        //exact: true,
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
      }
    ]}
  />

AnnouncementResource.propTypes = {
  path: T.string.isRequired,
  posts: T.arrayOf(
    T.shape(AnnouncementTypes.propTypes)
  ).isRequired,
  announcement: T.object.isRequired,
  openDetail: T.func.isRequired,
  resetDetail: T.func.isRequired,
  resetForm: T.func.isRequired
}

export {
  AnnouncementResource
}
