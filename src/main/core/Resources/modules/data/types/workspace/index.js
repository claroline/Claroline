import {trans} from '#/main/app/intl/translation'

import {WorkspaceCell} from '#/main/core/data/types/workspace/components/cell'
import {WorkspaceDisplay} from '#/main/core/data/types/workspace/components/display'
import {WorkspaceInput} from '#/main/core/data/types/workspace/components/input'
import {WorkspaceFilter} from '#/main/core/data/types/workspace/components/filter'

const dataType = {
  name: 'workspace',
  meta: {
    icon: 'fa fa-fw fa fa-books',
    label: trans('workspace', {}, 'data'),
    description: trans('workspace_desc', {}, 'data')
  },
  components: {
    details: WorkspaceDisplay,
    input: WorkspaceInput,
    table: WorkspaceCell,
    search: WorkspaceFilter
  }
}

export {
  dataType
}
