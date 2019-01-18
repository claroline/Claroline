import {trans} from '#/main/app/intl/translation'

import {FileDisplay} from '#/main/app/data/file/components/display'
import {FileGroup} from '#/main/core/layout/form/components/group/file-group'

const dataType = {
  name: 'file',
  meta: {
    creatable: true,
    icon: 'fa fa-fw fa fa-file-o',
    label: trans('file', {}, 'data'),
    description: trans('file_desc', {}, 'data')
  },
  components: {
    table: FileDisplay,
    details: FileDisplay,
    form: FileGroup
  }
}

export {
  dataType
}
