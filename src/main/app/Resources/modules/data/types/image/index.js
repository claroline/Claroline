import {trans} from '#/main/app/intl/translation'

import {ImageInput} from '#/main/app/data/types/image/components/input'

const dataType = {
  name: 'image',
  meta: {
    icon: 'fa fa-fw fa-picture-o',
    label: trans('image', {}, 'data'),
    description: trans('image_desc', {}, 'data')
  },
  components: {
    input: ImageInput
  }
}

export {
  dataType
}
