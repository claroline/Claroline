import {declareTool} from '#/main/core/tool'

import {CommunityTool} from '#/main/community/tools/community/containers/tool'

/**
 * Community tool.
 *
 * It is available for the Desktop and Workspace contexts.
 * It is used to manage users registrations and other user related entities (eg. groups, roles, organizations).
 */
export default declareTool(CommunityTool)
