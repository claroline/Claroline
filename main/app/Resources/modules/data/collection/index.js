import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/core/translation'
import {chain, array} from '#/main/core/validation'
import {validateProp} from '#/main/app/content/form/validator'

import {CollectionGroup} from '#/main/app/data/collection/components/group'
import {CollectionInput} from '#/main/app/data/collection/components/input'

// TODO : implement min/max

const dataType = {
  name: 'collection',
  meta: {
    icon: 'fa fa-fw fa-th',
    label: trans('collection'),
    description: trans('collection_desc')
  },

  validate: (value, options = {}) => {
    return chain(value, options, [array, (value) => {
      if (value) {
        const errors = {}

        value.map((item, index) => {
          // call correct type validator for all items
          const error = validateProp({
            type: options.type,
            required: true,
            options: options.options
          })

          if (error) {
            errors[index] = error
          }
        })

        if (!isEmpty(errors)) {
          return errors
        }
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
