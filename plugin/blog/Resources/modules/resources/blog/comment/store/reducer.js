import {makeReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import isEmpty from 'lodash/isEmpty'
import cloneDeep from 'lodash/cloneDeep'
import {
  SHOW_COMMENTS, 
  SHOW_COMMENT_FORM,
  SHOW_EDIT_COMMENT_FORM,
  CREATE_POST_COMMENT,
  UPDATE_POST_COMMENT,
  DELETE_POST_COMMENT
} from '#/plugin/blog/resources/blog/comment/store/actions'
import {
  POST_LOAD,
  POST_RESET
} from '#/plugin/blog/resources/blog/post/store/actions'

const reducer = {
  showComments: makeReducer(true, {
    [SHOW_COMMENTS]: (state, action) => action.value
  }),
  showCommentForm: makeReducer(false, {
    [SHOW_COMMENT_FORM]: (state, action) => action.value
  }),
  showEditCommentForm: makeReducer('', {
    [SHOW_EDIT_COMMENT_FORM]: (state, action) => action.value
  }),
  comments: makeListReducer('comments', {
    sortBy: {
      property: 'creationDate',
      direction: -1
    }
  }, {
    data: makeReducer(false, {
      [POST_RESET]: () => ([]),
      [UPDATE_POST_COMMENT]: (state, action) => {
        if(!isEmpty(state)){
          const data = cloneDeep(state)
          const commentIndex = data.findIndex(e => e.id === action.comment.id)
          data[commentIndex] = action.comment
          return data
        }
        return {}
      },
      [CREATE_POST_COMMENT]: (state, action) => {
        const data = cloneDeep(state)
        data.unshift(action.comment)
        return data
      },
      [DELETE_POST_COMMENT]: (state, action) => {
        if(!isEmpty(state)){
          const data = cloneDeep(state)
          const commentIndex = data.findIndex(e => e.id === action.commentId)
          data.splice(commentIndex, 1)
          return data
        }
        return {}
      }
    }),
    invalidated: makeReducer(false, {
      [POST_LOAD]: () => true,
      [POST_RESET]: () => true
    })
  }, {selectable: false}
  )
}

export {
  reducer
}