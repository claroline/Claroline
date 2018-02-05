import React from 'react'
import {PropTypes as T} from 'prop-types'

import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'
import {Image} from '#/main/core/layout/form/components/field/image.jsx'

const ImageGroup = props =>
  <FormGroup {...props}>
    <Image {...props} />
  </FormGroup>

ImageGroup.propTypes = {
  controlId: T.string.isRequired,
  value: T.object,
  disabled: T.bool.isRequired,
  onChange: T.func.isRequired
}

ImageGroup.defaultProps = {
  value: '',
  disabled: false,
  onChange: () => {}
}

export {
  ImageGroup
}
