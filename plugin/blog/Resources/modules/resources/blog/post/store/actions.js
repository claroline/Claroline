import {API_REQUEST} from '#/main/app/api'
import {makeActionCreator} from '#/main/core/scaffolding/actions'
import {constants} from '#/plugin/blog/resources/blog/constants.js'
import {now} from '#/main/core/scaffolding/date'
import {actions as formActions} from '#/main/core/data/form/actions'
import {actions as blogActions} from '#/plugin/blog/resources/blog/store/actions'

export const POSTS_LOAD = 'POSTS_LOAD'
export const POST_LOAD = 'POST_LOAD'
export const POST_RESET = 'POST_RESET'
export const POST_DELETE = 'POST_DELETE'
export const POST_UPDATE_PUBLICATION = 'POST_UPDATE_PUBLICATION'
export const POST_EDIT_RESET = 'POST_EDIT_RESET'
export const INIT_DATALIST = 'INIT_DATALIST'
  
export const actions = {}
  
actions.initDataList = makeActionCreator(INIT_DATALIST)
actions.postsLoad = makeActionCreator(POSTS_LOAD, 'posts')
actions.postLoad = makeActionCreator(POST_LOAD, 'post')
actions.postDelete = makeActionCreator(POST_DELETE, 'postId')
actions.postReset = makeActionCreator(POST_RESET)
actions.updatePostPublicationState = makeActionCreator(POST_UPDATE_PUBLICATION, 'post')

actions.getPost = (blogId, postId) => (dispatch) => {
  dispatch(actions.postReset())
  dispatch({[API_REQUEST]: {
    url:['apiv2_blog_post_get', {blogId, postId}],
    request: {
      method: 'GET'
    },
    success: (response, dispatch) => dispatch(actions.postLoad(response))
  }})
}

actions.editPost = (formName, blogId, postId) => (dispatch) => {
  //reset form from previous data
  dispatch(formActions.resetForm(formName, {}, true))
  if (postId) {
    dispatch({
      [API_REQUEST]: {
        url:['apiv2_blog_post_get', {blogId, postId}],
        request: {
          method: 'GET'
        },
        success: (response, dispatch) => {
          dispatch(formActions.resetForm(formName, response, false))
          dispatch(blogActions.switchMode(constants.EDIT_POST))
        }
      }
    })
  }
}

actions.createPost = (formName) => (dispatch) => {
  dispatch(formActions.resetForm(formName, {publicationDate: now()}, true))
  dispatch(blogActions.switchMode(constants.CREATE_POST))
}

actions.publishPost = (blogId, postId) => {
  return {[API_REQUEST]: {
    url:['apiv2_blog_post_publish', {blogId, postId}],
    request: {
      method: 'PUT'
    },
    success: (response, dispatch) => {
      dispatch(actions.updatePostPublicationState(response))
    }
  }}
}

actions.pinPost = (blogId, postId) => {
  return {[API_REQUEST]: {
    url:['apiv2_blog_post_pin', {blogId, postId}],
    request: {
      method: 'PUT'
    },
    success: (response, dispatch) => {
      dispatch(actions.updatePostPublicationState(response))
    }
  }}
}

actions.deletePost = (blogId, postId) => {
  return {[API_REQUEST]: {
    url:['apiv2_blog_post_delete', {blogId, postId}],
    request: {
      method: 'DELETE'
    },
    success: (response, dispatch) => {
      dispatch(actions.postDelete(postId))
    }
  }}
}
