import {makeReducer, combineReducers} from '#/main/core/scaffolding/reducer'
import {FORM_SUBMIT_SUCCESS} from '#/main/core/data/form/actions'
import {LIST_FILTER_ADD, LIST_FILTER_REMOVE} from '#/main/core/data/list/actions'
import {reducer as editorReducer} from '#/plugin/blog/resources/blog/editor/store'
import {reducer as postReducer} from '#/plugin/blog/resources/blog/post/store'
import {reducer as commentReducer} from '#/plugin/blog/resources/blog/comment/store'
import {reducer as toolbarReducer} from '#/plugin/blog/resources/blog/toolbar/store'
import {SWITCH_MODE} from '#/plugin/blog/resources/blog/store/actions'

const reducer = {
  calendarSelectedDate: makeReducer('', {
    [LIST_FILTER_ADD+'/posts']: (state, action) => {
      if(action.property === 'publicationDate'){
        return action.value
      }
      return state
    },
    [LIST_FILTER_REMOVE+'/posts']: (state, action) => {
      if(action.filter.property === 'publicationDate'){
        return null
      }
      return state
    }
  }),
  goHome: makeReducer(false, {
    [FORM_SUBMIT_SUCCESS+'/post_edit']: () => true,
    [SWITCH_MODE]: () => false
  }),
  user: makeReducer({}, {}),
  showComments: commentReducer.showComments,
  showCommentForm: commentReducer.showCommentForm,
  showEditCommentForm: commentReducer.showEditCommentForm,
  mode: makeReducer('list_posts', {
    [SWITCH_MODE]: (state, action) => action.mode
  }),
  posts: postReducer.posts,
  post: postReducer.post,
  post_edit: postReducer.post_edit,
  resourceNode: makeReducer({}, {}),
  blog: combineReducers({
    data: combineReducers({
      id: makeReducer({}, {}),
      title: makeReducer({}, {}),
      authors: toolbarReducer.authors,
      archives: makeReducer({}, {}),
      tags: toolbarReducer.tags,
      options: editorReducer.options
    })
  })
}

export {reducer}