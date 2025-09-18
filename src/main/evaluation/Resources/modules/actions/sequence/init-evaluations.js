import {constants, declareAction} from '#/main/app/action'
import {trans} from '#/main/app/intl'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {hasPermission} from '#/main/app/security'

export default declareAction((sequences, refresher) => ({
  name: 'init-evaluations',
  type: ASYNC_BUTTON,
  label: trans('initialize_evaluations', {}, 'actions'),
  request: {
    url: ['apiv2_sequence_evaluation_init', {sequenceId: sequences[0].id}],
    request: {
      method: 'PUT'
    },
    success: () => refresher.update(sequences)
  },
  displayed: hasPermission('follow', sequences[0]),
  score: ['object'],
  set: [constants.ACTION_SET_ADVANCED, constants.ACTION_SET_DASHBOARD],
  title: trans('initialize_evaluations', {}, 'actions'),
  description: trans('initialize_sequence_evaluations_help', {}, 'actions'),
  labelShort: trans('initialize', {}, 'actions')
}))
