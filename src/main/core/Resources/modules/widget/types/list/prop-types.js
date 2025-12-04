import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {ListParameters} from '#/main/app/content/list/parameters/prop-types'
import {WidgetInstance} from '#/main/core/widget/content/prop-types'

const ListWidgetParameters = implementPropTypes({}, ListParameters, {
  filterContext: T.bool,
  maxResults: T.number
}, {
  filterContext: true
})

const ListWidget = implementPropTypes({}, WidgetInstance, {
  parameters: T.shape(ListWidgetParameters.propTypes)
}, {
  parameters: ListWidgetParameters.defaultProps
})

export {
  ListWidget,
  ListWidgetParameters
}
