import {trans} from '#/main/app/intl/translation'

import {WorkspacesDisplay} from '#/main/core/data/types/workspaces/components/display'
import {WorkspacesInput} from '#/main/core/data/types/workspaces/components/input'
import {WorkspacesFilter} from '#/main/core/data/types/workspaces/components/filter'

const dataType = {
  name: 'workspaces',
  meta: {
    icon: 'fa fa-fw fa fa-books',
    label: trans('workspaces', {}, 'data'),
    description: trans('workspaces_desc', {}, 'data')
  },
  components: {
    details: WorkspacesDisplay,
    input: WorkspacesInput,
    search: WorkspacesFilter
  }
}

export {
  dataType
}
