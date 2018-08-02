import merge from 'lodash/merge'

import {makeReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import {ListWidget} from '#/main/core/widget/types/list/components/widget'
import {WIDGET_UPDATE_CONFIG} from '#/main/core/widget/types/list/actions'

/**
 * List widget application.
 *
 * @param {object} context    - the context of widget rendering
 * @param {object} parameters - the current widget parameters
 *
 * @constructor
 */
export const App = (context, parameters = {}) => ({
  component: ListWidget,
  store: {
    config: makeReducer({}, {
      [WIDGET_UPDATE_CONFIG]: (state, action) => merge({}, state, action.config)
    }),
    list: makeListReducer('list', {}, {
      invalidated: makeReducer(false, {
        // we invalidate the list when target url is changed to force reloading
        [WIDGET_UPDATE_CONFIG]: (state, action) => !!action.config.fetchUrl
      })
    }, {
      selectable: false,
      filterable: parameters.filterable,
      sortable: parameters.sortable,
      paginated: parameters.paginated
    })
  },
  initialData: () => ({ // function is for retro compatibility with bootstrap()
    config: parameters,
    list: parameters.paginated ? {
      pageSize: parameters.pageSize
    } : {}
  })
})
