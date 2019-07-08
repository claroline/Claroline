import isEmpty from 'lodash/isEmpty'
import {chain, array, string, notBlank, lengthInRange} from '#/main/app/data/types/validators'

import {CriteriaGroup} from '#/plugin/drop-zone/data/criteria/components/group'
import {CriteriaInput} from '#/plugin/drop-zone/data/criteria/components/input'

// TODO : replace by a criterion type and use `collection`

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
    group: CriteriaGroup,
    input: CriteriaInput
  }
}

export {
  dataType
}
