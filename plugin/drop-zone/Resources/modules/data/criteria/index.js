import isEmpty from 'lodash/isEmpty'
import {chain, array, string, notBlank, lengthInRange} from '#/main/core/validation'

import {CriteriaGroup} from '#/plugin/drop-zone/data/criteria/components/form-group'

const dataType = {
  name: 'criteria',
  validate: (value, options) => chain(value, options, [array, lengthInRange, (value) => {
    if (value) {
      const errors = {}

      value.map((criterion, index) => {
        const error = chain(criterion.instruction, {isHtml: true}, [string, notBlank])
        if (error) {
          errors[index] = error
        }
      })

      if (!isEmpty(errors)) {
        return errors
      }
    }
  }]),

  components: {
    form: CriteriaGroup
  }
}

export {
  dataType
}
