/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Team plugin.
 */
registry.add('ClarolineTeamBundle', {
  tools: {
    'claroline_team_tool': () => { return import(/* webpackChunkName: "plugin-team-tool" */ '#/plugin/team/tools/team') }
  }
})
