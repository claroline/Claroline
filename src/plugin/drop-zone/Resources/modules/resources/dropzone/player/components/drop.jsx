import React from 'react'
import {PropTypes as T} from 'prop-types'

import {DropType} from '#/plugin/drop-zone/resources/dropzone/prop-types'

const Drop = () =>
  <div className="dropzone-drop">

  </div>

Drop.propTypes = {
  drop: T.shape(
    DropType.propTypes
  ).isRequired
}

export {
  Drop
}
