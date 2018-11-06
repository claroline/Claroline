import {trans} from '#/main/app/intl/translation'

import {WorkspaceDisplay} from '#/main/core/data/types/workspace/components/display'
import {WorkspaceGroup} from '#/main/core/data/types/workspace/components/group'

const dataType = {
  name: 'workspace',
  meta: {
    creatable: false,
    icon: 'fa fa-fw fa fa-books',
    label: trans('workspace'),
    description: trans('workspace_desc')
  },
  components: {
    details: WorkspaceDisplay,
    form: WorkspaceGroup
  }
}

export {
  dataType
}
