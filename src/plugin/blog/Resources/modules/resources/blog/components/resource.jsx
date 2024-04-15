import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {url} from '#/main/app/api'
import {LINK_BUTTON, URL_BUTTON, DOWNLOAD_BUTTON} from '#/main/app/buttons'

import {Resource, ResourcePage} from '#/main/core/resource'

import {Moderation} from '#/plugin/blog/resources/blog/moderation/components/moderation'
import {Player} from '#/plugin/blog/resources/blog/player/components/player'

const BlogResource = props =>
  <Resource
    {...omit(props)}
    styles={['claroline-distribution-plugin-blog-blog-resource']}
  >
    <ResourcePage
      primaryAction="blog_post"
      customActions={[
        {
          name: 'moderation',
          displayed : props.canEdit || props.canModerate,
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-gavel',
          label: trans('moderate', {}, 'actions'),
          target: `${props.path}/moderation/posts`,
          group: trans('management')
        }, {
          name: 'export-pdf',
          type: DOWNLOAD_BUTTON,
          icon: 'fa fa-fw fa-file-pdf',
          label: trans('export-pdf', {}, 'actions'),
          displayed: props.canExport,
          group: trans('transfer'),
          file: {
            url: ['icap_blog_pdf', {blogId: props.blogId}]
          }
        }, {
          type: URL_BUTTON,
          icon: 'fa fa-fw fa-rss',
          label: trans('show_rss', {}, 'actions'),
          target: url(['icap_blog_rss', {blogId: props.blogId}])
        }
      ]}
      routes={[
        {
          path: '/moderation',
          component: Moderation
        }, {
          path: '/',
          component: Player
        }
      ]}
    />
  </Resource>

BlogResource.propTypes = {
  path: T.string.isRequired,
  blogId: T.string.isRequired,
  canEdit: T.bool,
  canPost: T.bool,
  canModerate: T.bool,
  canExport: T.bool
}

export {
  BlogResource
}
