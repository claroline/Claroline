import {hasPermission} from '#/main/app/security'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {trans, transChoice} from '#/main/app/intl/translation'
import {declareAction} from '#/main/app/action'

export default declareAction((events, refresher) => {
  const processable = events.filter(event => hasPermission('delete', event))

  return {
    name: 'delete',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-trash',
    label: trans('delete', {}, 'actions'),
    displayed: 0 !== processable.length,
    dangerous: true,
    confirm: {
      message: transChoice('event_delete_confirm', processable.length, {count: '<b class="fw-bold">'+processable.length+'</b>'}, 'agenda'),
      additional: trans('irreversible_action_confirm'),
      items:  processable.map(item => ({
        thumbnail: item.poster,
        id: item.id,
        name: item.name
      }))
    },
    request: {
      url: ['apiv2_event_delete'],
      request: {
        method: 'DELETE',
        body: JSON.stringify(processable.map(event => event.id))
      },
      success: () => refresher.delete(processable)
    },
    group: trans('management'),
    scope: ['object', 'collection']
  }
})
