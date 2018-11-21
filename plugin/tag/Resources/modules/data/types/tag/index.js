import {trans} from '#/main/app/intl/translation'

import {TagCell} from '#/plugin/tag/data/tag/components/cell'
import {TagDisplay} from '#/plugin/tag/data/tag/components/display'
import {TagFilter} from '#/plugin/tag/data/tag/components/filter'
import {TagGroup} from '#/plugin/tag/data/tag/components/group'

// todo : finish implementation
// todo : validation

const dataType = {
  name: 'tag',
  meta: {
    creatable: false,
    icon: 'fa fa-fw fa-tag',
    label: trans('tag', {}, 'data'),
    description: trans('tag_desc', {}, 'data')
  },
  components: {
    details: TagDisplay,
    table: TagCell,
    search: TagFilter,
    form: TagGroup
  }
}

export {
  dataType
}
