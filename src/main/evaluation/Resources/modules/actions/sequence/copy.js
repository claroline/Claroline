import {MODAL_BUTTON} from '#/main/app/buttons'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {constants, declareAction} from '#/main/app/action'
import {MODAL_SEQUENCE_COPY} from '#/main/evaluation/sequence/modals/copy'
import {MODAL_WORKSPACES} from '#/main/core/modals/workspaces'

/**
 * Creates a copy of sequences chosen by the user.
 *
 * @param {Array}  sequences  - the list of sequences on which we want to execute the action.
 * @param {object} refresher - an object containing methods to update context in response to action (eg. add, update, delete).
 */
export default declareAction((sequences, refresher) => {
  const processable = sequences.filter(sequence => hasPermission('edit', sequence))

  return ({
    name: 'copy',
    type: MODAL_BUTTON,
    icon: 'fa fa-fw fa-clone',
    label: trans('copy', {}, 'actions'),
    displayed: 0 !== processable.length,
    modal: [MODAL_WORKSPACES, {
      multiple: false,
      selectAction: (selected) => ({
        type: MODAL_BUTTON,
        modal: [MODAL_SEQUENCE_COPY, {
          workspace: selected[0],
          sequences: processable,
          onCopy: () => refresher.update(processable)
        }]
      })
    }],
    group: trans('management'),
    scope: ['object', 'collection'],
    set: [constants.ACTION_SET_LIST, constants.ACTION_SET_DETAILS]
  })
})
