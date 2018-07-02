import {makeReducer} from '#/main/core/scaffolding/reducer'
import {
  SHOW_COMMENTS, 
  SHOW_COMMENT_FORM,
  SHOW_EDIT_COMMENT_FORM
} from '#/plugin/blog/resources/blog/comment/store/actions'

const reducer = {
  showComments: makeReducer(true, {
    [SHOW_COMMENTS]: (state, action) => action.value
  }),
  showCommentForm: makeReducer(false, {
    [SHOW_COMMENT_FORM]: (state, action) => action.value
  }),
  showEditCommentForm: makeReducer('', {
    [SHOW_EDIT_COMMENT_FORM]: (state, action) => action.value
  })
}

export {
  reducer
}