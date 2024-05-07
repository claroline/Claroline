import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {SlideshowResource as SlideshowResourceComponent} from '#/plugin/slideshow/resources/slideshow/components/resource'
import {reducer, selectors} from '#/plugin/slideshow/resources/slideshow/store'

const SlideshowResource = withReducer(selectors.STORE_NAME, reducer)(SlideshowResourceComponent)

export {
  SlideshowResource
}
