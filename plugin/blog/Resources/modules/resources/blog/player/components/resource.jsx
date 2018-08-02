import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {url} from '#/main/app/api'
import {ResourcePageContainer} from '#/main/core/resource/containers/page.jsx'
import {RoutedPageContent} from '#/main/core/layout/router'
import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {selectors as resourceSelect} from '#/main/core/resource/store'
import {hasPermission} from '#/main/core/resource/permissions'
import {DOWNLOAD_BUTTON, LINK_BUTTON, URL_BUTTON} from '#/main/app/buttons'

import {Moderation} from '#/plugin/blog/resources/blog/moderation/components/moderation'
import {Player} from '#/plugin/blog/resources/blog/player/components/player'

const Blog = props =>
  <ResourcePageContainer
    primaryAction="post"
    customActions={[
      {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-home',
        label: trans('show_overview'),
        target: '/',
        exact: true
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        displayed: props.canEdit || props.canPost,
        label: trans('new_post', {}, 'icap_blog'),
        target: '/new',
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
    <RoutedPageContent className={'blog-page'}routes={[
      {
        path: '/moderation',
        component: Moderation
      },{
        path: '/',
        component: Player
      }
    ]}/>
  </ResourcePageContainer>

Blog.propTypes = {
  blogId: T.string.isRequired,
  saveEnabled: T.bool.isRequired,
  pdfEnabled: T.bool.isRequired,
  canEdit: T.bool,
  canPost: T.bool,
  canModerate: T.bool,
  canExport: T.bool
}
          
const BlogContainer = connect(
  state => ({
    blogId: state.blog.data.id,
    saveEnabled: formSelect.saveEnabled(formSelect.form(state, 'blog.data.options')),
    pdfEnabled: state.pdfenabled,
    canExport: hasPermission('export', resourceSelect.resourceNode(state)),
    canEdit: hasPermission('edit', resourceSelect.resourceNode(state)),
    canPost: hasPermission('post', resourceSelect.resourceNode(state)),
    canModerate: hasPermission('moderate', resourceSelect.resourceNode(state))
  })
)(Blog)
      
export {BlogContainer}
