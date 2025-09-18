import {constants, declareAction} from '#/main/app/action'
import {trans} from '#/main/app/intl'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {hasPermission} from '#/main/app/security'

export default declareAction((sequences, refresher) => ({
  name: 'recompute_evaluation',
  label: trans('recompute_evaluations', {}, 'actions'),
  type: ASYNC_BUTTON,
  request: {
    url: ['apiv2_sequence_evaluation_recompute', {sequenceId: sequences[0].id}],
    request: {
      method: 'PUT'
    },
    success: () => refresher.update(sequences)
  },
  displayed: hasPermission('follow', sequences[0]),
  group: trans('evaluation'),
  scope: ['object'],
  set: [constants.ACTION_SET_ADVANCED, constants.ACTION_SET_DASHBOARD],
  title: trans('recompute_evaluations', {}, 'actions'),
  description: trans('recompute_sequence_evaluations_help', {}, 'actions'),
  labelShort: trans('recalculate', {}, 'actions')
}))
