import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {route} from '#/main/evaluation/sequence'
import {declareAction} from '#/main/app/action'

export default declareAction((evaluations, refresher, path) => ({
  name: 'open-sequence',
  type: LINK_BUTTON,
  icon: 'fa fa-fw fa-route',
  label: trans('open_sequence', {}, 'actions'),
  target: route(evaluations[0].sequence, null, path),
  scope: ['object'],
  exact: true,
  default: true
}))
