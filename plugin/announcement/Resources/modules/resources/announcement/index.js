import {reducer} from '#/plugin/announcement/resources/announcement/store'
import {AnnouncementResource} from '#/plugin/announcement/resources/announcement/containers/resource'

/**
 * Announcement resource application.
 *
 * @constructor
 */
export default {
  component: AnnouncementResource,
  store: reducer,
  styles: ['claroline-distribution-plugin-announcement-announcement-resource']
}
