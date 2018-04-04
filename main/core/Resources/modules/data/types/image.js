import {trans} from '#/main/core/translation'

import {ImageGroup} from '#/main/core/layout/form/components/group/image-group.jsx'

const IMAGE_TYPE = 'image'

// todo finish implementation

const imageDefinition = {
  meta: {
    type: IMAGE_TYPE,
    creatable: false,
    icon: 'fa fa-fw fa-picture-o',
    label: trans('image'),
    description: trans('image_desc')
  },
  components: {
    form: ImageGroup
  }
}

export {
  IMAGE_TYPE,
  imageDefinition
}
