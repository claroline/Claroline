import {RssFeedCreation} from '#/plugin/rss/resources/rss-feed/containers/creation'
import {RssFeedResource} from '#/plugin/rss/resources/rss-feed/containers/resource'

/**
 * @constructor
 */
export const Creation = () => ({
  component: RssFeedCreation
})

/**
 * @constructor
 */
export const App = () => ({
  component: RssFeedResource
})
