import {reducer} from '#/plugin/slideshow/resources/slideshow/store'
import {SlideshowResource} from '#/plugin/slideshow/resources/slideshow/containers/resource'

/**
 * Slideshow resource application.
 */
export default {
  component: SlideshowResource,
  store: reducer,
  styles: ['claroline-distribution-plugin-slideshow-slideshow-resource']
}
