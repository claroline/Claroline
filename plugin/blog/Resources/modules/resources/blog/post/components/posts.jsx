import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {DataListContainer} from '#/main/core/data/list/containers/data-list.jsx'
import {constants as listConst} from '#/main/core/data/list/constants'
import {trans} from '#/main/core/translation'
import {Button} from '#/main/app/action/components/button'
import {PostCard} from '#/plugin/blog/resources/blog/post/components/post.jsx'
import {hasPermission} from '#/main/core/resource/permissions'
import isEmpty from 'lodash/isEmpty'
import {selectors as resourceSelect} from '#/main/core/resource/store'

const PostsList = props =>
  <div className={'posts-list'}>
    {(props.canEdit || props.canPost) &&
      <Button
        icon={'fa fa-fw fa-plus'}
        label={trans('new_post', {}, 'icap_blog')}
        type="link"
        className="btn blog-primary-button"
        target={'/new'}
      />
    }
    <DataListContainer
      name="posts"
      fetch={{
        url: ['apiv2_blog_post_list', {blogId: props.blogId}],
        autoload: true
      }}
      open={{
        action: (row) => `#/${row.slug}`
      }}
      definition={[
        {
          name: 'title',
          label: trans('title', {}, 'platform'),
          type: 'string',
          primary: true,
          displayed: true
        },{
          name: 'publicationDate',
          label: trans('icap_blog_post_form_publicationDate', {}, 'icap_blog'),
          type: 'date',
          displayed: true
        },{
          name: 'fromDate',
          label: trans('icap_blog_post_form_publicationDateFrom', {}, 'icap_blog'),
          type: 'date',
          sortable: false
        },{
          name: 'toDate',
          label: trans('icap_blog_post_form_publicationDateTo', {}, 'icap_blog'),
          type: 'date',
          sortable: false
        },{
          name: 'content',
          label: trans('content', {}, 'platform'),
          type: 'string',
          sortable: false,
          displayed: false
        },{
          name: 'authorName',
          label: trans('author', {}, 'platform'),
          type: 'string'
        },{
          name: 'tags',
          label: trans('tags', {}, 'platform'),
          type: 'string',
          sortable: false,
          displayed: false
        }
      ]}

      selection={{}}
      card={PostCard}
    
      display={{
        available : [listConst.DISPLAY_LIST],
        current: listConst.DISPLAY_LIST
      }}
    />
    {!isEmpty(props.posts) &&
      <Button
        icon={'fa fa-4x fa-arrow-circle-up'}
        label={trans('go-up', {}, 'icap_blog')}
        type="callback"
        tooltip="bottom"
        callback={() => props.goUp()}
        className="btn-link button-go-to-top pull-right"
        target={'/new'}
      />
    }
  </div>

PostsList.propTypes ={
  goUp: T.func.isRequired,
  blogId: T.string.isRequired,
  posts: T.array,
  canEdit: T.bool,
  canPost: T.bool
}

const PostsContainer = connect(
  state => ({
    posts: state.posts.data,
    blogId: state.blog.data.id,
    canEdit: hasPermission('edit', resourceSelect.resourceNode(state)),
    canPost: hasPermission('post', resourceSelect.resourceNode(state))
  }),
  () => ({
    goUp: () => {
      let node = document.getElementById('blog-top-page')
      if (node) {
        node.scrollIntoView({block: 'end', behavior: 'smooth', inline: 'center'})
      }
    }
  })
)(PostsList)

export {PostsContainer as Posts}