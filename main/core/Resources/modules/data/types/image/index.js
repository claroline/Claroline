import {t} from '#/main/core/translation'

import {ImageGroup} from '#/main/core/layout/form/components/group/image-group.jsx'

const IMAGE_TYPE = 'image'

// todo implement

const imageDefinition = {
  meta: {
    type: IMAGE_TYPE,
    creatable: true,
    icon: 'fa fa-fw fa fa-picture-o',
    label: t('image'),
    description: t('image_desc')
  },
  // nothing special to do
  parse: (display) => display,
  // nothing special to do
  render: (raw) => raw,
  validate: (value) => typeof value === 'string',
  components: {
    form: ImageGroup
  }
}

export {
  IMAGE_TYPE ,
  imageDefinition
}
