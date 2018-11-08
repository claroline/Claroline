import React from 'react'
import classes from 'classnames'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

const TagInput = props =>
  <div className="tag-input">
    Tag input
  </div>

implementPropTypes(TagInput, FormFieldTypes, {

}, {

})

export {
  TagInput
}
