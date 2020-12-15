import {reducer} from '#/plugin/rss/resources/rss-feed/store'
import {RssFeedCreation} from '#/plugin/rss/resources/rss-feed/containers/creation'
import {RssFeedResource} from '#/plugin/rss/resources/rss-feed/containers/resource'

/**
 * @constructor
 */
export const Creation = () => ({
  component: RssFeedCreation
})

export default {
  component: RssFeedResource,
  store: reducer
}
