import {connect} from 'react-redux'

import {selectors as contentSelectors} from '#/main/core/widget/content/store'
import {SimpleWidget as SimpleWidgetComponent} from '#/main/core/widget/types/simple/components/widget'

const SimpleWidget = connect(
  (state) => ({
    contentRaw: contentSelectors.parameters(state).contentRaw,
    content: contentSelectors.parameters(state).content
  })
)(SimpleWidgetComponent)

export {
  SimpleWidget
}
