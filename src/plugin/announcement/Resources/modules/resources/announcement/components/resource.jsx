import React from 'react'
import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'
import omit from 'lodash/omit'

import {makeId} from '#/main/core/scaffolding/id'
import {Resource} from '#/main/core/resource'

import {Announcement as AnnouncementTypes} from '#/plugin/announcement/resources/announcement/prop-types'
import {AnnounceForm} from '#/plugin/announcement/resources/announcement/components/announce-form'
import {AnnouncementEditor} from '#/plugin/announcement/resources/announcement/components/editor'
import {AnnouncementOverview} from '#/plugin/announcement/resources/announcement/containers/overview'
import {AnnouncementPost} from '#/plugin/announcement/resources/announcement/containers/post'

const AnnouncementResource = props =>
  <Resource
    {...omit(props)}
    styles={['claroline-distribution-plugin-announcement-announcement-resource']}
    editor={AnnouncementEditor}
    overviewPage={AnnouncementOverview}
    pages={[
      {
        path: '/add',
        component: AnnounceForm,
        onEnter: () => props.resetForm(merge({}, AnnouncementTypes.defaultProps, {
          id: makeId()
        }), true)
      }, {
        path: '/:id',
        component: AnnouncementPost,
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
