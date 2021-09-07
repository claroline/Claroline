import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {url} from '#/main/app/api'
import {LINK_BUTTON, URL_BUTTON, CALLBACK_BUTTON} from '#/main/app/buttons'

// TODO : avoid hard dependency
import html2pdf from 'html2pdf.js'

import {ResourcePage} from '#/main/core/resource/containers/page'

import {Moderation} from '#/plugin/blog/resources/blog/moderation/components/moderation'
import {Player} from '#/plugin/blog/resources/blog/player/components/player'

const BlogResource = props =>
  <ResourcePage
    primaryAction="blog_post"
    customActions={[
      {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-home',
        label: trans('show_overview'),
        target: props.path,
        exact: true
      }, {
        displayed : props.canEdit || props.canModerate,
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-gavel',
        label: trans('moderate', {}, 'actions'),
        target: `${props.path}/moderation/posts`,
        group: trans('management')
      }, {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-file-pdf-o',
        label: trans('export-pdf', {}, 'actions'),
        displayed: props.canExport,
        group: trans('transfer'),
        callback: () => props.downloadBlogPdf(props.blogId).then(pdfContent => {
          html2pdf()
            .set({
              filename: pdfContent.name,
              image: { type: 'jpeg', quality: 1 },
              html2canvas: { scale: 4 },
              enableLinks: true
            })
            .from(pdfContent.content, 'string')
            .save()
        })
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

BlogResource.propTypes = {
  path: T.string.isRequired,
  blogId: T.string.isRequired,
  downloadBlogPdf: T.func.isRequired,
  saveEnabled: T.bool.isRequired,
  canEdit: T.bool,
  canPost: T.bool,
  canModerate: T.bool,
  canExport: T.bool
}

export {
  BlogResource
}
