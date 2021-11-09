import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {hasPermission} from '#/main/app/security'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {TextResource as TextResourceComponent} from '#/main/core/resources/text/components/resource'
import {reducer, selectors} from '#/main/core/resources/text/store'

const TextResource = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      canExport: hasPermission('export', resourceSelectors.resourceNode(state)),
      text: selectors.text(state)
    })
  )(TextResourceComponent)
)

export {
  TextResource
}
