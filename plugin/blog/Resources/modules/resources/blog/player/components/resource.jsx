import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {ResourcePageContainer} from '#/main/core/resource/containers/page.jsx'
import {RoutedPageContent} from '#/main/core/layout/router'
import {PageContent} from '#/main/core/layout/page/index'
import {trans} from '#/main/core/translation'
import {select as formSelect} from '#/main/core/data/form/selectors'
import {actions as formActions} from '#/main/core/data/form/actions'
import {url} from '#/main/app/api'
import {Moderation} from '#/plugin/blog/resources/blog/moderation/components/moderation.jsx'
import {Player} from '#/plugin/blog/resources/blog/player/components/player.jsx'
import {selectors as resourceSelect} from '#/main/core/resource/store'
import {actions as toolbarActions} from '#/plugin/blog/resources/blog/toolbar/store'
import {constants} from '#/plugin/blog/resources/blog/constants.js'
import {hasPermission} from '#/main/core/resource/permissions'

const Blog = props =>
  <ResourcePageContainer
    editor={{
      icon: 'fa fa-pencil',
      label: trans('configure_blog', {}, 'icap_blog'),
      path: '/edit',
      save: {
        disabled: !props.saveEnabled,
        action: () => {
          props.saveOptions(props.blogId)
        }
      }
    }}
    customActions={[
      {
        type: 'link',
        icon: 'fa fa-fw fa-home',
        label: trans('show_overview'),
        target: '/',
        exact: true
      },{
        type: 'link',
        icon: 'fa fa-fw fa-plus',
        displayed: props.canEdit || props.canPost,
        label: trans('new_post', {}, 'icap_blog'),
        target: '/new',
        exact: true
      },{
        displayed : props.canEdit || props.canModerate,
        type: 'link',
        icon: 'fa fa-fw fa-gavel',
        label: trans('moderation', {}, 'icap_blog'),
        target: '/moderation/posts'
      },{
        displayed : props.pdfEnabled && props.canExport,
        type: 'download',
        icon: 'fa fa-fw fa-file-pdf-o',
        label: trans('pdf_export', {}, 'platform'),
        file: {
          url: url(['icap_blog_pdf', {blogId: props.blogId}])
        }
      },{
        type: 'url',
        icon: 'fa fa-fw fa-rss',
        label: trans('rss_label', {}, 'icap_blog'),
        target: url(['icap_blog_rss', {blogId: props.blogId}])
      }
    ]}
  >
    <PageContent>
      <div id={'blog-top-page'}></div>
      <RoutedPageContent className="blog-page-content" routes={[
        {
          path: '/moderation',
          component: Moderation
        },{
          path: '/',
          component: Player
        }
      ]}/>
    </PageContent>
  </ResourcePageContainer>

Blog.propTypes = {
  blogId: T.string.isRequired,
  saveEnabled: T.bool.isRequired,
  pdfEnabled: T.bool.isRequired,
  saveOptions: T.func.isRequired,
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
  }),
  dispatch => ({
    saveOptions: (blogId) => {
      dispatch(
        formActions.saveForm(constants.OPTIONS_EDIT_FORM_NAME, ['apiv2_blog_options_update', {blogId: blogId}])
      ).then(
        () => dispatch(toolbarActions.getTags(blogId)))
    }
  })
)(Blog)
      
export {BlogContainer}
