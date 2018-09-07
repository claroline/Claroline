import {trans} from '#/main/core/translation'
import {chain, array, lengthInRange} from '#/main/core/validation'
import {validateProp} from '#/main/app/content/form/validator'

import {CollectionGroup} from '#/main/app/data/collection/components/group'
import {CollectionInput} from '#/main/app/data/collection/components/input'

const dataType = {
  name: 'collection',
  meta: {
    icon: 'fa fa-fw fa-th',
    label: trans('collection'),
    description: trans('collection_desc')
  },

  validate(value, options = {}) {
    return chain(value, options, [array, lengthInRange, (value) => {
      if (value) {
        return Promise
          .all(
            value.map((item) => validateProp({
              type: options.type,
              required: true,
              options: options.options
            }, item))
          )
      }
    }])
  },

  components: {
    form: CollectionGroup, // old version. to remove

    input: CollectionInput
  }
}

export {
  dataType
}
