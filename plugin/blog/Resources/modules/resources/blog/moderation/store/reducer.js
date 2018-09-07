import {makeListReducer} from '#/main/app/content/list/store'
import {makeReducer} from '#/main/app/store/reducer'
import {selectors} from '#/plugin/blog/resources/blog/store/selectors'

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
  }),
  trustedUsers: makeReducer([])
}

export {reducer}
