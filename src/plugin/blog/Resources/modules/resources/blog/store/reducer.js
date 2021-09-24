import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {SEARCH_FILTER_ADD, SEARCH_FILTER_REMOVE} from '#/main/app/content/search/store/actions'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {SWITCH_MODE} from '#/plugin/blog/resources/blog/store/actions'
import {selectors} from '#/plugin/blog/resources/blog/store/selectors'
import {reducer as editorReducer} from '#/plugin/blog/resources/blog/editor/store/reducer'
import {reducer as postReducer} from '#/plugin/blog/resources/blog/post/store/reducer'
import {reducer as commentReducer} from '#/plugin/blog/resources/blog/comment/store/reducer'
import {reducer as toolbarReducer} from '#/plugin/blog/resources/blog/toolbar/store/reducer'
import {reducer as moderationReducer} from '#/plugin/blog/resources/blog/moderation/store/reducer'

const reducer = combineReducers({
  calendarSelectedDate: makeReducer('', {
    [SEARCH_FILTER_ADD + '/' + selectors.STORE_NAME + '.posts']: (state, action) => {
      if(action.property === 'publicationDate'){
        return action.value
      }
      return state
    },
    [SEARCH_FILTER_REMOVE + '/' + selectors.STORE_NAME + '.posts']: (state, action) => {
      if(action.filter.property === 'publicationDate'){
        return null
      }
      return state
    }
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
  moderationComments: moderationReducer.moderationComments,
  reportedComments: moderationReducer.reportedComments,
  moderationPosts: moderationReducer.moderationPosts,
  trustedUsers: moderationReducer.trustedUsers,
  blog: combineReducers({
    data: combineReducers({
      id: makeReducer('', {
        [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.blog.id || state
      }),
      title: makeReducer('', {
        [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.blog.title || state
      }),
      authors: toolbarReducer.authors,
      archives: makeReducer({}, {
        [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.archives || state
      }),
      tags: toolbarReducer.tags,
      options: editorReducer.options
    })
  })
})

export {
  reducer
}
