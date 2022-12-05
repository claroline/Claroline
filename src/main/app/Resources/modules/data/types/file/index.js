import {trans} from '#/main/app/intl/translation'

import {FileDisplay} from '#/main/app/data/types/file/components/display'
import {FileInput} from '#/main/app/data/types/file/components/input'

const dataType = {
  name: 'file',
  meta: {
    creatable: true,
    icon: 'fa fa-fw fa fa-file',
    label: trans('file', {}, 'data'),
    description: trans('file_desc', {}, 'data')
  },
  components: {
    table: FileDisplay,
    details: FileDisplay,
    input: FileInput
  }
}

export {
  dataType
}
