import {createElement} from 'react'

import {hasPermission} from '#/main/app/security'
import {url} from '#/main/app/api'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {trans, transChoice} from '#/main/app/intl/translation'

import {TeamCard} from '#/main/community/team/components/card'

/**
 * Delete teams action.
 */
export default (teams, refresher) => {
  const processable = teams.filter(team => hasPermission('delete', team))

  return {
    name: 'delete',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-trash',
    label: trans('delete', {}, 'actions'),
    displayed: 0 !== processable.length,
    dangerous: true,
    confirm: {
      title: transChoice('team_delete_confirm_title', processable.length, {}, 'community'),
      subtitle: 1 === processable.length ? processable[0].name : transChoice('count_elements', processable.length, {count: processable.length}),
      message: transChoice('team_delete_confirm_message', processable.length, {count: processable.length}, 'community'),
      additional: [
        createElement('div', {
          key: 'additional',
          className: 'modal-body'
        }, processable.map(team => createElement(TeamCard, {
          key: team.id,
          orientation: 'row',
          size: 'xs',
          data: team
        })))
      ]
    },
    request: {
      url: url(['apiv2_team_delete_bulk'], {ids: processable.map(team => team.id)}),
      request: {
        method: 'DELETE'
      },
      success: () => refresher.delete(processable)
    },
    group: trans('management'),
    scope: ['object', 'collection']
  }
}
