import {hasPermission} from '#/main/app/security'
import {url} from '#/main/app/api'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl/translation'

/**
 * Delete evidence action.
 */
export default (presences, refresher) => {
  const processable = presences.filter(presence => hasPermission('edit', presence))

  return {
    name: 'delete-evidence',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-trash',
    label: trans('delete_evidence', {}, 'presence'),
    displayed: 0 !== processable.length && (processable[0].evidences && processable[0].evidences.length > 0),
    dangerous: true,
    confirm: {
      title: trans('delete_evidence', {}, 'presence'),
      message: trans('delete_evidence_message', {}, 'presence')
    },
    request: {
      url: url(['apiv2_cursus_presence_evidence_delete', {id: processable[0].id}]),
      request: {
        method: 'DELETE'
      },
      success: () => refresher.delete(processable)
    },
    group: trans('management'),
    scope: ['object']
  }
}
