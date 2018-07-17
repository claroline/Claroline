import {API_REQUEST} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'

export const SHOW_COMMENTS = ' SHOW_COMMENTS'
export const SHOW_COMMENT_FORM = ' SHOW_COMMENT_FORM'
export const SHOW_EDIT_COMMENT_FORM = ' SHOW_EDIT_COMMENT_FORM'
export const CREATE_COMMENT = ' CREATE_COMMENT'
export const UPDATE_POST_COMMENT = 'UPDATE_POST_COMMENT'
export const REPORT_POST_COMMENT = 'REPORT_POST_COMMENT'
export const CREATE_POST_COMMENT = 'CREATE_POST_COMMENT'
export const DELETE_POST_COMMENT = 'DELETE_POST_COMMENT'

  
export const actions = {}

actions.showComments = makeActionCreator(SHOW_COMMENTS, 'value')
actions.showCommentForm = makeActionCreator(SHOW_COMMENT_FORM, 'value')
actions.showEditCommentForm = makeActionCreator(SHOW_EDIT_COMMENT_FORM, 'value')
actions.updateComment = makeActionCreator(UPDATE_POST_COMMENT, 'comment')
actions.reportedComment = makeActionCreator(REPORT_POST_COMMENT, 'comment')
actions.createComment = makeActionCreator(CREATE_POST_COMMENT, 'comment')
actions.removeComment = makeActionCreator(DELETE_POST_COMMENT, 'commentId')

actions.submitComment = (blogId, postId, comment) => (dispatch) => {
  dispatch({
    [API_REQUEST]: {
      url: ['apiv2_blog_comment_new', {blogId, postId}],
      request: {
        method: 'POST',
        body: JSON.stringify({comment: comment})
      },
      success: (response, dispatch) => {
        dispatch(actions.showCommentForm(false))
        dispatch(actions.createComment(response))
      }
    }
  })
}

actions.editComment = (blogId, commentId, comment) => (dispatch) => {
  dispatch({
    [API_REQUEST]: {
      url: ['apiv2_blog_comment_update', {blogId, commentId}],
      request: {
        method: 'PUT',
        body: JSON.stringify({comment: comment})
      },
      success: (response, dispatch) => {
        dispatch(actions.showEditCommentForm(''))
        dispatch(actions.updateComment(response))
      }
    }
  })
}

actions.publishComment = (blogId, commentId) => (dispatch) => {
  dispatch({
    [API_REQUEST]: {
      url: ['apiv2_blog_comment_publish', {blogId, commentId}],
      request: {
        method: 'PUT'
      },
      success: (response, dispatch) => {
        dispatch(actions.updateComment(response))
      }
    }
  })
}

actions.reportComment = (blogId, commentId) => (dispatch) => {
  dispatch({
    [API_REQUEST]: {
      url: ['apiv2_blog_comment_report', {blogId, commentId}],
      request: {
        method: 'PUT'
      },
      success: (response, dispatch) => {
        dispatch(actions.reportedComment(response))
      }
    }
  })
}

actions.unpublishComment = (blogId, commentId) => (dispatch) => {
  dispatch({
    [API_REQUEST]: {
      url: ['apiv2_blog_comment_unpublish', {blogId, commentId}],
      request: {
        method: 'PUT'
      },
      success: (response, dispatch) => {
        dispatch(actions.updateComment(response))
      }
    }
  })
}

actions.deleteComment = (blogId, commentId) => (dispatch) => {
  dispatch({
    [API_REQUEST]: {
      url: ['apiv2_blog_comment_delete', {blogId, commentId}],
      request: {
        method: 'DELETE'
      },
      success: (response, dispatch) => {
        dispatch(actions.removeComment(commentId))
      }
    }
  })
}
