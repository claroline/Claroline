import {makeListReducer} from '#/main/app/content/list/store'
import {makeReducer} from '#/main/app/store/reducer'
import {selectors} from '#/plugin/blog/resources/blog/store/selectors'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
import {
  POST_UPDATE_PUBLICATION
} from '#/plugin/blog/resources/blog/post/store/actions'
import {
  UPDATE_POST_COMMENT,
  DELETE_POST_COMMENT,
  REPORT_POST_COMMENT
} from '#/plugin/blog/resources/blog/comment/store/actions'

const reducer = {
  moderationComments: makeListReducer(selectors.STORE_NAME + '.moderationComments', {
    sortBy: {
      property: 'creationDate',
      direction: -1
    }
  }, {
    invalidated: makeReducer(false, {
      [UPDATE_POST_COMMENT]: () => true,
      [DELETE_POST_COMMENT]: () => true
    })
  }),
  reportedComments: makeListReducer(selectors.STORE_NAME + '.reportedComments', {
    sortBy: {
      property: 'creationDate',
      direction: -1
    }
  }, {
    invalidated: makeReducer(false, {
      [REPORT_POST_COMMENT]: () => true,
      [DELETE_POST_COMMENT]: () => true
    })
  }),
  moderationPosts: makeListReducer(selectors.STORE_NAME + '.moderationPosts', {
    sortBy: {
      property: 'creationDate',
      direction: -1
    }
  }, {
    invalidated: makeReducer(false, {
      [POST_UPDATE_PUBLICATION]: () => true,
      [FORM_SUBMIT_SUCCESS+'/' + selectors.STORE_NAME + '.post_edit']: () => true
    })
  }),
  trustedUsers: makeReducer([])
}

export {reducer}
