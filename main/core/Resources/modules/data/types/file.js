import {trans} from '#/main/core/translation'

import {FileGroup} from '#/main/core/layout/form/components/group/file-group.jsx'

const FILE_TYPE = 'file'

// todo implement

const fileDefinition = {
  meta: {
    type: FILE_TYPE,
    creatable: true,
    icon: 'fa fa-fw fa fa-file-o',
    label: trans('file'),
    description: trans('file_desc')
  },
  validate: () => {},
  components: {
    form: FileGroup
  }
}

export {
  FILE_TYPE,
  fileDefinition
}
