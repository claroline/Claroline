import {trans} from '#/main/core/translation'

import {DownloadLink} from '#/main/core/layout/button/components/download-link'
import {FileGroup} from '#/main/core/layout/form/components/group/file-group'

const FILE_TYPE = 'file'

// todo finish implement

const fileDefinition = {
  meta: {
    type: FILE_TYPE,
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
  FILE_TYPE,
  fileDefinition
}
