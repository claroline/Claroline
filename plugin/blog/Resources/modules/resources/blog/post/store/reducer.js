import {makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {makeInstanceAction} from '#/main/app/store/actions'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {
  INIT_DATALIST,
  POST_LOAD,
  POST_DELETE,
  POST_RESET,
  POST_UPDATE_PUBLICATION
} from '#/plugin/blog/resources/blog/post/store/actions'
import {selectors} from '#/plugin/blog/resources/blog/store/selectors'

const reducer = {
  posts: makeListReducer(selectors.STORE_NAME + '.posts', {
    sortBy: {
      property: 'publicationDate',
      direction: -1
    }
  }, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS+'/' + selectors.STORE_NAME + '.post_edit']: () => true,
      [FORM_SUBMIT_SUCCESS+'/' + selectors.STORE_NAME + '.blog.data.options']: () => true,
      [POST_UPDATE_PUBLICATION]: () => true,
      [INIT_DATALIST]: () => true,
      [POST_DELETE]: () => true,
      [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: () => true
    })
  }),
  post: makeReducer({}, {
    [POST_LOAD]: (state, action) => action.post,
    [POST_UPDATE_PUBLICATION]: (state, action) => action.post,
    [POST_RESET]: () => ({})
  }),
  post_edit: makeFormReducer(selectors.STORE_NAME + '.post_edit')
}

export {
  reducer
}
