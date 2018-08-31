import {trans} from '#/main/core/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_RESOURCE_FILES_CREATION} from '#/main/core/resource/modals/files'

const action = (resourceNodes, nodesRefresher) => ({
  name: 'add_files',
  type: MODAL_BUTTON,
  label: trans('add_files', {}, 'resource'),
  icon: 'fa fa-fw fa-file-upload',
  primary: true,
  modal: [MODAL_RESOURCE_FILES_CREATION, {
    parent: resourceNodes[0],
    add: (newNodes) => nodesRefresher.add(newNodes)
  }]
})

export {
  action
}
