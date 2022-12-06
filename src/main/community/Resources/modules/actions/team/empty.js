import {trans} from '#/main/app/intl'
import {url} from '#/main/app/api'
import {hasPermission} from '#/main/app/security'
import {ASYNC_BUTTON} from '#/main/app/buttons'

export default (teams, refresher) => {
  const processable = teams.filter(team => hasPermission('edit', team) && team.users > 0)

  return {
    name: 'empty',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-user-minus',
    label: trans('empty', {}, 'actions'),
    displayed: 0 !== processable.length,
    request: {
      url: url(['apiv2_team_empty'], {ids: processable.map(team => team.id)}),
      request: {
        method: 'DELETE'
      },
      success: () => refresher.update(processable)
    },
    dangerous: true,
    group: trans('registration'),
    scope: ['object', 'collection']
  }
}
