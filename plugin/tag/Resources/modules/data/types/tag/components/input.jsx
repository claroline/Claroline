import React from 'react'

import {implementPropTypes} from '#/main/app/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

const TagInput = () =>
  <div className="tag-input">
    Tag input
  </div>

implementPropTypes(TagInput, FormFieldTypes, {

}, {

})

export {
  TagInput
}
