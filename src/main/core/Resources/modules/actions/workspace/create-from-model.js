import get from 'lodash/get'
import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {constants, declareAction} from '#/main/app/action'

export default declareAction((workspaces) => ({
  name: 'create-from-model',
  type: CALLBACK_BUTTON,
  icon: 'fa fa-fw fa-plus',
  label: trans('create_from_model', {}, 'actions'),
  callback: () => true,
  displayed: get(workspaces[0], 'meta.model', false) && !get(workspaces[0], 'meta.archived', false),
  scope: ['object'],
  set: [constants.ACTION_SET_LIST, constants.ACTION_SET_DETAILS]
}))
