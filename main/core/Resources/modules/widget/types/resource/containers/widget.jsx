import {connect} from 'react-redux'

import {ResourceWidget as ResourceWidgetComponent} from '#/main/core/widget/types/resource/components/widget'

import {selectors as contentSelectors} from '#/main/core/widget/content/store'

const ResourceWidget = connect(
  (state) => ({
    resourceNode: contentSelectors.parameters(state).resource,
    showResourceHeader: contentSelectors.parameters(state).showResourceHeader
  })
)(ResourceWidgetComponent)

export {
  ResourceWidget
}
