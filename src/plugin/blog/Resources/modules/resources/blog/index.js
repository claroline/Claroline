import get from 'lodash/get'
import merge from 'lodash/merge'

import {declareResource} from '#/main/core/resource'
import {BlogResource} from '#/plugin/blog/resources/blog/containers/resource'

/**
 * Blog resource application.
 */
export default declareResource(BlogResource, {
  create(resourceData = {resource: {}, resourceNode: {}}) {
    // initializes the blog_post rights for all the roles which can
    // edit this resource. We do it here to allow the user to reset it directly into the
    // rights section of the creation form
    const newNode = merge({}, resourceData.resourceNode)
    newNode.rights = get(resourceData, 'resourceNode.rights', []).map(roleRights => {
      let customPerms = {}
      if (roleRights.permissions.edit) {
        customPerms['blog_post'] = true
      }

      return merge({}, roleRights, {permissions: customPerms})
    })

    return {
      resource: resourceData.resource,
      resourceNode: newNode
    }
  }
})
