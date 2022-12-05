import {trans} from '#/main/app/intl/translation'

import {ImageInput} from '#/main/app/data/types/image/components/input'
import {ImageDisplay} from '#/main/app/data/types/image/components/display'

const dataType = {
  name: 'image',
  meta: {
    icon: 'fa fa-fw fa-picture',
    label: trans('image', {}, 'data'),
    description: trans('image_desc', {}, 'data')
  },
  components: {
    input: ImageInput,
    display: ImageDisplay
  }
}

export {
  dataType
}
