import {registry} from '#/main/app/plugins/registry'

registry.add('team', {
  tools: {
    'claroline_team_tool': () => { return import(/* webpackChunkName: "plugin-team-tool" */ '#/plugin/team/tools/team') }
  }
})