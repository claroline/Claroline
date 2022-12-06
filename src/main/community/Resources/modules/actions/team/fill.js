import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {url} from '#/main/app/api'
import {hasPermission} from '#/main/app/security'
import {ASYNC_BUTTON} from '#/main/app/buttons'

export default (teams, refresher) => {
  const processable = teams.filter(team => hasPermission('edit', team))

  return {
    name: 'fill',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-user-plus',
    label: trans('fill', {}, 'actions'),
    displayed: 0 !== processable.length,
    disabled: !processable.find(team => !get(team, 'restrictions.users') || team.users < get(team, 'restrictions.users')),
    request: {
      url: url(['apiv2_team_fill'], {ids: processable.map(team => team.id)}),
      request: {
        method: 'PUT'
      },
      success: () => refresher.update(processable)
    },
    group: trans('registration'),
    scope: ['object', 'collection']
  }
}
