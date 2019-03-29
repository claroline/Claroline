import React from 'react'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

const MediasInput = props =>
  <div>
    MEDIAS
  </div>

implementPropTypes(MediasInput, FormFieldTypes, {
  value: T.arrayOf(T.shape({
    // TODO
  }))
}, {
  value: []
})

export {
  MediasInput
}
