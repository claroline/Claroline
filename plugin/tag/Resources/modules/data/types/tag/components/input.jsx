import React from 'react'
import {PropTypes as T} from 'prop-types'

import {implementPropTypes} from '#/main/app/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

const TagInput = (props) =>
  <div className="tag-input">
    Tag input
  </div>

implementPropTypes(TagInput, FormFieldTypes, {
  value: T.arrayOf(T.string)
}, {

})

export {
  TagInput
}
