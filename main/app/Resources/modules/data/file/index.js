import {trans} from '#/main/core/translation'

import {DownloadLink} from '#/main/core/layout/button/components/download-link'
import {FileGroup} from '#/main/core/layout/form/components/group/file-group'

const dataType = {
  name: 'file',
  meta: {
    creatable: true,
    icon: 'fa fa-fw fa fa-file-o',
    label: trans('file'),
    description: trans('file_desc')
  },
  components: {
    table: DownloadLink,
    details: DownloadLink,
    form: FileGroup
  }
}

export {
  dataType
}
