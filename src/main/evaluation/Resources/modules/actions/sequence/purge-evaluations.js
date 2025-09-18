import {constants, declareAction} from '#/main/app/action'
import {trans} from '#/main/app/intl'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {hasPermission} from '#/main/app/security'

export default declareAction((sequences, refresher) => ({
  name: 'purge_evaluation',
  label: trans('purge_evaluations', {}, 'actions'),
  type: ASYNC_BUTTON,
  confirm: {
    message: trans('purge_sequence_evaluations_confirm', {}, 'actions'),
    additional: trans('irreversible_action_confirm')
  },
  request: {
    url: ['apiv2_sequence_evaluation_purge', {sequenceId: sequences[0].id}],
    request: {
      method: 'DELETE'
    },
    success: () => refresher.update(sequences)
  },
  displayed: hasPermission('administrate', sequences[0]),
  group: trans('evaluation'),
  scope: ['object'],
  set: [constants.ACTION_SET_ADVANCED, constants.ACTION_SET_DASHBOARD],
  dangerous: true,
  title: trans('purge_evaluations', {}, 'actions'),
  description: trans('purge_sequence_evaluations_help', {}, 'actions'),
  labelShort: trans('purge', {}, 'actions'),
  managerOnly: true
}))
