import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_WORKSPACE_COPY} from '#/main/core/workspace/modals/copy'

export default (workspaces, refresher) => {
  const processable = workspaces.filter(workspace => hasPermission('administrate', workspace))

  return {
    name: 'copy',
    type: MODAL_BUTTON,
    icon: 'fa fa-fw fa-clone',
    label: trans('copy', {}, 'actions'),
    displayed: 0 !== processable.length,
    modal: [MODAL_WORKSPACE_COPY, {
      workspaces: processable,
      onCopy: (response) => refresher.update(response)
    }],
    group: trans('management'),
    scope: ['object', 'collection']
  }
}
