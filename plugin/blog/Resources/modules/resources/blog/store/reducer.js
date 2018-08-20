import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
import {LIST_FILTER_ADD, LIST_FILTER_REMOVE} from '#/main/app/content/list/store/actions'
import {reducer as editorReducer} from '#/plugin/blog/resources/blog/editor/store'
import {reducer as postReducer} from '#/plugin/blog/resources/blog/post/store'
import {reducer as commentReducer} from '#/plugin/blog/resources/blog/comment/store'
import {reducer as toolbarReducer} from '#/plugin/blog/resources/blog/toolbar/store'
import {reducer as moderationReducer} from '#/plugin/blog/resources/blog/moderation/store'
import {SWITCH_MODE} from '#/plugin/blog/resources/blog/store/actions'
import {selectors} from '#/plugin/blog/resources/blog/store/selectors'
import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

const reducer = combineReducers({
  calendarSelectedDate: makeReducer('', {
    [LIST_FILTER_ADD + '/' + selectors.STORE_NAME + '.posts']: (state, action) => {
      if(action.property === 'publicationDate'){
        return action.value
      }
      return state
    },
    [LIST_FILTER_REMOVE + '/' + selectors.STORE_NAME + '.posts']: (state, action) => {
      if(action.filter.property === 'publicationDate'){
        return null
      }
      return state
    }
  }),
  goHome: makeReducer(false, {
    [FORM_SUBMIT_SUCCESS + '/' + selectors.STORE_NAME + '.post_edit']: () => true,
    [SWITCH_MODE]: () => false
  }),
  user: makeReducer({}, {
    [RESOURCE_LOAD]: (state, action) => action.resourceData.user || state
  }),
  mode: makeReducer(selectors.STORE_NAME + '.list_posts', {
    [SWITCH_MODE]: (state, action) => action.mode
  }),
  posts: postReducer.posts,
  comments: commentReducer.comments,
  showComments: commentReducer.showComments,
  showCommentForm: commentReducer.showCommentForm,
  showEditCommentForm: commentReducer.showEditCommentForm,
  post: postReducer.post,
  post_edit: postReducer.post_edit,
  resourceNode: makeReducer({}, {}),
  moderationComments: moderationReducer.moderationComments,
  reportedComments: moderationReducer.reportedComments,
  moderationPosts: moderationReducer.moderationPosts,
  trustedUsers: moderationReducer.trustedUsers,
  blog: combineReducers({
    data: combineReducers({
      id: makeReducer({}, {
        [RESOURCE_LOAD]: (state, action) => action.resourceData.blog.id || state
      }),
      title: makeReducer({}, {
        [RESOURCE_LOAD]: (state, action) => action.resourceData.blog.title || state
      }),
      authors: toolbarReducer.authors,
      archives: makeReducer({}, {
        [RESOURCE_LOAD]: (state, action) => action.resourceData.archives || state
      }),
      tags: toolbarReducer.tags,
      options: editorReducer.options
    })
  })
})

export {
  reducer
}
