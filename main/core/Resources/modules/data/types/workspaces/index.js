import {trans} from '#/main/core/translation'

import {WorkspacesDisplay} from '#/main/core/data/types/workspaces/components/display'
import {WorkspacesGroup} from '#/main/core/data/types/workspaces/components/group'

const dataType = {
  name: 'workspaces',
  meta: {
    creatable: false,
    icon: 'fa fa-fw fa fa-books',
    label: trans('workspaces'),
    description: trans('workspaces_desc')
  },
  components: {
    details: WorkspacesDisplay,
    form: WorkspacesGroup
  }
}

export {
  dataType
}
