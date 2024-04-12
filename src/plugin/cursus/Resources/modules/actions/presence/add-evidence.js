import {MODAL_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'
import {MODAL_EVIDENCE} from '#/plugin/cursus/modals/presence/evidences'

export default (presences, refresher) => {
  const processable = presences.filter(presence => hasPermission('edit', presence))

  return {
    name: 'add-evidence',
    type: MODAL_BUTTON,
    icon: 'fa fa-fw fa-file-upload',
    label: trans('add_evidences', {}, 'presence'),
    modal: [MODAL_EVIDENCE, {
      parent: processable[0],
      onSuccess: refresher.update,
      editable: true
    }],
    displayed: 0 !== processable.length,
    group: trans('validation', {}, 'presence'),
    scope: ['object']
  }
}
