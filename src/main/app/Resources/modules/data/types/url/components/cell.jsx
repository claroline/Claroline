import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Button} from '#/main/app/action/components/button'
import {URL_BUTTON} from '#/main/app/buttons'

const UrlCell = (props) =>
  <Button
    type={URL_BUTTON}
    label={props.data}
    target={props.data}
    primary={true}
  />

UrlCell.propTypes = {
  data: T.string
}

export {
  UrlCell
}
