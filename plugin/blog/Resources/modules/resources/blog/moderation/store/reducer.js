import {makeListReducer} from '#/main/app/content/list/store'
import {makeReducer} from '#/main/app/store/reducer'
import {select} from '#/plugin/blog/resources/blog/selectors'

import {
  UPDATE_POST_COMMENT,
  DELETE_POST_COMMENT,
  REPORT_POST_COMMENT
} from '#/plugin/blog/resources/blog/comment/store/actions'

const reducer = {
  moderationComments: makeListReducer(select.STORE_NAME + '.moderationComments', {
    sortBy: {
      property: 'creationDate',
      direction: -1
    }
  }, {
    invalidated: makeReducer(false, {
      [UPDATE_POST_COMMENT]: () => true,
      [DELETE_POST_COMMENT]: () => true
    })
  }, {selectable: false}
  ),
  reportedComments: makeListReducer(select.STORE_NAME + '.reportedComments', {
    sortBy: {
      property: 'creationDate',
      direction: -1
    }
  }, {
    invalidated: makeReducer(false, {
      [REPORT_POST_COMMENT]: () => true,
      [DELETE_POST_COMMENT]: () => true
    })
  }, {selectable: false}
  ),
  moderationPosts: makeListReducer(select.STORE_NAME + '.moderationPosts', {
    sortBy: {
      property: 'creationDate',
      direction: -1
    }
  }, {}, {selectable: false}
  ),
  trustedUsers: makeReducer([], {})
}

export {reducer}
