import {trans} from '#/main/app/intl/translation'

import {TagCell} from '#/plugin/tag/data/types/tag/components/cell'
import {TagDisplay} from '#/plugin/tag/data/types/tag/components/display'
import {TagFilter} from '#/plugin/tag/data/types/tag/components/filter'
import {TagInput} from '#/plugin/tag/data/types/tag/components/input'

// todo : finish implementation
// todo : validation

const dataType = {
  name: 'tag',
  meta: {
    icon: 'fa fa-fw fa-tag',
    label: trans('tag', {}, 'data'),
    description: trans('tag_desc', {}, 'data')
  },
  components: {
    details: TagDisplay,
    table: TagCell,
    search: TagFilter,
    input: TagInput
  }
}

export {
  dataType
}
