import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {ListParameters} from '#/main/app/content/list/prop-types'
import {WidgetInstance} from '#/main/core/widget/content/prop-types'

const ListWidgetParameters = implementPropTypes({}, ListParameters, {
  maxResults: T.number
})

const ListWidget = implementPropTypes({}, WidgetInstance, {
  parameters: ListWidgetParameters.propTypes
}, {
  parameters: ListWidgetParameters.defaultProps
})

export {
  ListWidget,
  ListWidgetParameters
}
