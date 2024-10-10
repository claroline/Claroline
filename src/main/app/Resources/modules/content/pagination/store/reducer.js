import {makeInstanceReducer} from '#/main/app/store/reducer'

import {constants} from '#/main/app/content/pagination/constants'
import {
  PAGINATION_PAGE_CHANGE,
  PAGINATION_SIZE_UPDATE
} from '#/main/app/content/pagination/store/actions'

const defaultState = {
  page: 0,
  pageSize: constants.DEFAULT_PAGE_SIZE
}

const reducer = makeInstanceReducer(defaultState, {
  /**
   * Changes the current page.
   *
   * @param {Object} state
   * @param {Object} action
   *
   * @returns {Object}
   */
  [PAGINATION_PAGE_CHANGE]: (state, action) => ({
    page: action.page,
    pageSize: state.pageSize
  }),

  /**
   * Changes the page size.
   *
   * @param {Object} state
   * @param {Object} action
   *
   * @returns {Object}
   */
  [PAGINATION_SIZE_UPDATE]: (state, action) => ({
    page: state.page,
    pageSize: action.pageSize
  })
})

export {
  reducer
}
