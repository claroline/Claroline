import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {hasPermission} from '#/main/app/security/permissions'

import {SlideshowResource as SlideshowResourceComponent} from '#/plugin/slideshow/resources/slideshow/components/resource'
import {reducer, selectors} from '#/plugin/slideshow/resources/slideshow/store'

const SlideshowResource = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      showOverview: selectors.showOverview(state),
      editable: hasPermission('edit', resourceSelectors.resourceNode(state))
    })
  )(SlideshowResourceComponent)
)

export {
  SlideshowResource
}
