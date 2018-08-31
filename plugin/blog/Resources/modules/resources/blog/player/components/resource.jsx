import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {url} from '#/main/app/api'
import {ResourcePage} from '#/main/core/resource/containers/page'
import {RoutedPageContent} from '#/main/core/layout/router'

import {DOWNLOAD_BUTTON, LINK_BUTTON, URL_BUTTON} from '#/main/app/buttons'

import {Moderation} from '#/plugin/blog/resources/blog/moderation/components/moderation'
import {Player} from '#/plugin/blog/resources/blog/player/components/player'

const Blog = props =>
  <ResourcePage
    styles={['claroline-distribution-plugin-blog-blog-resource']}
    primaryAction="blog_post"
    customActions={[
      {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-home',
        label: trans('show_overview'),
        target: '/',
        exact: true
      },{
        displayed : props.canEdit || props.canModerate,
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-gavel',
        label: trans('moderation', {}, 'icap_blog'),
        target: '/moderation/posts'
      },{
        displayed : props.pdfEnabled && props.canExport,
        type: DOWNLOAD_BUTTON,
        icon: 'fa fa-fw fa-file-pdf-o',
        label: trans('pdf_export', {}, 'platform'),
        file: {
          url: url(['icap_blog_pdf', {blogId: props.blogId}])
        }
      },{
        type: URL_BUTTON,
        icon: 'fa fa-fw fa-rss',
        label: trans('rss_label', {}, 'icap_blog'),
        target: url(['icap_blog_rss', {blogId: props.blogId}])
      }
    ]}
  >
    <div id={'blog-top-page'}></div>
    <RoutedPageContent
      className={'blog-page'}
      routes={[
        {
          path: '/moderation',
          component: Moderation
        }, {
          path: '/',
          component: Player
        }
      ]}/>
  </ResourcePage>

Blog.propTypes = {
  blogId: T.string.isRequired,
  saveEnabled: T.bool.isRequired,
  pdfEnabled: T.bool.isRequired,
  canEdit: T.bool,
  canPost: T.bool,
  canModerate: T.bool,
  canExport: T.bool
}

export {Blog}
