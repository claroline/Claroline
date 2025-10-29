import {declareAction} from '#/main/app/action'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {trans, transChoice} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'
import {url} from '#/main/app/api'

export default declareAction((events, refresher) => {
  const processable = events.filter(event => hasPermission('edit', event))

  return ({
    name: 'copy',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-clone',
    label: trans('copy', {}, 'actions'),
    displayed: 0 !== processable.length,
    confirm: {
      message: transChoice('copy_event_confirm_message', processable.length, {count: '<b class="fw-bold">'+processable.length+'</b>'}, 'actions'),
      items:  processable.map(item => ({
        thumbnail: item.poster,
        id: item.id,
        name: item.name
      }))
    },
    request: {
      url: url(['apiv2_training_event_copy']),
      request: {
        method: 'POST',
        body: JSON.stringify(processable.map(row => row.id))
      },
      success: (response) => refresher.add(response)
    },
    group: trans('management'),
    scope: ['object', 'collection']
  })
})
