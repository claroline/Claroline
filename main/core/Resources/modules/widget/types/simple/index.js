import {makeReducer} from '#/main/app/store/reducer'

import {SimpleWidget} from '#/main/core/widget/types/simple/components/widget'
import {SimpleWidgetParameters} from '#/main/core/widget/types/simple/components/parameters'

export const Parameters = () => ({
  component: SimpleWidgetParameters
})

/**
 * Simple widget application.
 *
 * @param {object} context    - the context of widget rendering
 * @param {object} parameters - the current widget parameters
 *
 * @constructor
 */
export const App = (context, parameters) => ({
  component: SimpleWidget,
  store: {
    content: makeReducer(null, {})
  },
  initialData: () => ({ // function is for retro compatibility with bootstrap()
    content: parameters.content
  })
})
