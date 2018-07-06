import {makeListReducer} from '#/main/core/data/list/reducer'
import {makeReducer} from '#/main/core/scaffolding/reducer'
import {
  UPDATE_POST_COMMENT,
  DELETE_POST_COMMENT,
  REPORT_POST_COMMENT
} from '#/plugin/blog/resources/blog/comment/store/actions'

const reducer = {
  moderationComments: makeListReducer('moderationComments', {
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
  reportedComments: makeListReducer('reportedComments', {
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
  moderationPosts: makeListReducer('moderationPosts', {
    sortBy: {
      property: 'creationDate',
      direction: -1
    }
  }, {}, {selectable: false}
  ),
  trustedUsers: makeReducer([], {})
}

export {reducer}