import {reducer} from '#/plugin/announcement/resources/announcement/store'
import {AnnouncementResource} from '#/plugin/announcement/resources/announcement/containers/resource'
import {AnnouncementMenu} from '#/plugin/announcement/resources/announcement/components/menu'

/**
 * Announcement resource application.
 *
 * @constructor
 */
export default {
  component: AnnouncementResource,
  menu: AnnouncementMenu,
  store: reducer,
  styles: ['claroline-distribution-plugin-announcement-announcement-resource']
}
