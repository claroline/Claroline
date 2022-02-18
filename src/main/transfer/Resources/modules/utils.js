import {trans} from '#/main/app/intl/translation'

function transAction(actionName) {
  if (actionName) {
    return trans(actionName.substring(0, actionName.indexOf('_')), {}, 'transfer') + ' / ' + trans(actionName.substring(actionName.indexOf('_') + 1), {}, 'transfer')
  }

  return trans('unknown')
}

export {
  transAction
}
