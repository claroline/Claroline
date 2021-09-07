import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {hasPermission} from '#/main/app/security'
import {Routes} from '#/main/app/router'
import {selectors as resourceSelect} from '#/main/core/resource/store'

import {actions as editorActions} from '#/plugin/blog/resources/blog/editor/store'
import {actions as postActions} from '#/plugin/blog/resources/blog/post/store'
import {actions, selectors} from '#/plugin/blog/resources/blog/store'
import {constants} from '#/plugin/blog/resources/blog/constants'
import {Posts} from '#/plugin/blog/resources/blog/post/components/posts'
import {Post} from '#/plugin/blog/resources/blog/post/components/post'
import {PostForm} from '#/plugin/blog/resources/blog/post/components/post-form'
import {Tools} from '#/plugin/blog/resources/blog/toolbar/components/toolbar'
import {BlogOptions} from '#/plugin/blog/resources/blog/editor/components/blog-options'
import {initDatalistFilters} from '#/plugin/blog/resources/blog/utils'

const PlayerComponent = props =>
  <div className="row" style={{marginTop: 20}}>
    <div className="col-lg-9 col-md-8 col-sm-7 col-xs-12">
      <Routes
        path={props.path}
        routes={[
          {
            path: '/author/:authorId',
            component: Posts,
            exact: true,
            onEnter: (params) => props.getPostByAuthor(props.blogId, params.authorId)
          }, {
            path: '/new',
            component: PostForm,
            disabled: !props.canEdit,
            onEnter: () => props.createPost()
          }, {
            path: '/edit',
            disabled: !props.canEdit,
            component: BlogOptions,
            onEnter: () => props.editBlogOptions(props.blogId),
            exact: true
          }, {
            path: '/:id',
            component: Post,
            exact: true,
            onEnter: (params) => props.getPost(props.blogId, params.id)
          }, {
            path: '/:id/edit',
            component: PostForm,
            disabled: !props.canEdit,
            onEnter: (params) => props.editPost(props.blogId, params.id)
          }, {
            path: '/',
            component: Posts,
            exact: true,
            onEnter: () => {
              props.switchMode(constants.LIST_POSTS)
              props.initDataListFilters(props.location.search)
            }
          }
        ]}
      />
    </div>

    <div className="col-lg-3 col-md-4 col-sm-5 col-xs-12">
      <Tools />
    </div>
  </div>

PlayerComponent.propTypes = {
  path: T.string.isRequired,
  blogId: T.string.isRequired,
  postId: T.string,
  mode: T.string,
  canEdit: T.bool,
  getPost: T.func.isRequired,
  createPost: T.func.isRequired,
  getPostByAuthor: T.func.isRequired,
  editPost: T.func.isRequired,
  editBlogOptions: T.func.isRequired,
  switchMode: T.func.isRequired,
  initDataListFilters: T.func.isRequired,
  location: T.object
}

const Player = connect(
  state => ({
    path: resourceSelect.path(state),
    blogId: selectors.blog(state).data.id,
    postId: !isEmpty(selectors.postEdit(state)) ? selectors.postEdit(state).data.id : null,
    mode: selectors.mode(state),
    canEdit: hasPermission('edit', resourceSelect.resourceNode(state))
  }),
  dispatch => ({
    getPost: (blogId, postId) => {
      dispatch(postActions.getPost(blogId, postId))
    },
    createPost: () => {
      dispatch(postActions.createPost(selectors.STORE_NAME+'.'+constants.POST_EDIT_FORM_NAME))
    },
    getPostByAuthor: (blogId, authorName) => {
      dispatch(postActions.getPostByAuthor(blogId, authorName))
    },
    editPost: (blogId, postId) => {
      dispatch(postActions.editPost(selectors.STORE_NAME+'.'+constants.POST_EDIT_FORM_NAME, blogId, postId))
    },
    editBlogOptions: (blogId) => {
      dispatch(editorActions.editBlogOptions(selectors.STORE_NAME+'.'+constants.OPTIONS_EDIT_FORM_NAME, blogId))
    },
    switchMode: (mode) => {
      dispatch(actions.switchMode(mode))
    },
    initDataListFilters: (query) => {
      initDatalistFilters(dispatch, query)
    }
  })
)(PlayerComponent)

export {Player}
