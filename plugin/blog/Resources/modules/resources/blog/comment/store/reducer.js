import {makeReducer} from '#/main/core/scaffolding/reducer'
import {makeListReducer} from '#/main/core/data/list/reducer'
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
      [POST_RESET]: () => ([])
    }),
    invalidated: makeReducer(false, {
      [CREATE_POST_COMMENT]: () => true,
      [UPDATE_POST_COMMENT]: () => true,
      [DELETE_POST_COMMENT]: () => true,
      [POST_LOAD]: () => true,
      [POST_RESET]: () => true
    })
  }, {selectable: false}
  )
}

export {
  reducer
}