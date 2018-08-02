import {trans} from '#/main/core/translation'

import {ImageGroup} from '#/main/core/layout/form/components/group/image-group'

const dataType = {
  name: 'image',
  meta: {
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
  dataType
}
