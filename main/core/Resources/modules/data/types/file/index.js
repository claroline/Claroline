import {t} from '#/main/core/translation'

import {File} from '#/main/core/layout/form/components/field/file-upload.jsx'

const FILE_TYPE = 'file'

// todo implement

const fileDefinition = {
  meta: {
    type: FILE_TYPE,
    creatable: true,
    icon: 'fa fa-fw fa fa-file-o',
    label: t('file'),
    description: t('file_desc')
  },
  // nothing special to do
  parse: (display) => display,
  // nothing special to do
  render: (raw) => raw,
  validate: (value) => typeof value === 'string',
  components: {
    form: File
  }
}

export {
  FILE_TYPE,
  fileDefinition
}
