import {trans} from '#/main/app/intl/translation'
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
  }],
  displayed: resourceNodes[0].permissions.create.includes('file')
})

export {
  action
}
