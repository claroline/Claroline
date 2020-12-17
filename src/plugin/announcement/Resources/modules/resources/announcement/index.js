import get from 'lodash/get'
import merge from 'lodash/merge'

import {reducer} from '#/plugin/announcement/resources/announcement/store'
import {AnnouncementResource} from '#/plugin/announcement/resources/announcement/containers/resource'
import {AnnouncementMenu} from '#/plugin/announcement/resources/announcement/components/menu'

/**
 * Announcement resource application.
 */
export default {
  component: AnnouncementResource,
  menu: AnnouncementMenu,
  store: reducer,
  styles: ['claroline-distribution-plugin-announcement-announcement-resource'],

  create(resourceData = {resource: {}, resourceNode: {}}) {
    // initializes the create-announce rights for all the roles which can
    // edit this resource. We do it here to allow the user to reset it directly into the
    // rights section of the creation form
    const newNode = merge({}, resourceData.resourceNode)
    newNode.rights = get(resourceData, 'resourceNode.rights').map(roleRights => {
      let customPerms = {}
      if (roleRights.permissions.edit) {
        customPerms['create-announce'] = true
      }

      return merge({}, roleRights, {permissions: customPerms})
    })

    return {
      resource: resourceData.resource,
      resourceNode: newNode
    }
  }
}
