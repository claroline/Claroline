import get from 'lodash/get'

import {CLIPBOARD_BUTTON} from '#/main/app/buttons'
import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {declareAction} from '#/main/app/action'
import {route as toolRoute} from '#/main/core/tool/routing'

export default declareAction((events, refresher, path) => {
  const toolPath = toolRoute('trainings')
  const presenceUrl = `${window.location.href.split(path)[0]}${toolPath}/presence/${get(events[0], 'code')}`

  return {
    name: 'copy-presence-link',
    type: CLIPBOARD_BUTTON,
    icon: 'fa fa-fw fa-link',
    label: trans('copy_presence_link', {}, 'actions'),
    displayed: hasPermission('open', events[0]),
    copy: () => presenceUrl,
    scope: ['object']
  }
})
