import React from 'react'
import {PropTypes as T} from 'prop-types'

const Color = props =>
  <div
    className="color-input"
    style={Object.assign({}, props.style, {
      backgroundColor: props.color,
      color: props.label ? props.label.color : 'inherit'
    })}
  >
    {props.label &&
      props.label.text
    }

    <span className="color-value">{props.color}</span>
  </div>

Color.propTypes = {
  color: T.string.isRequired,
  label: T.shape({
    text: T.string.isRequired,
    color: T.string.isRequired
  }),
  style: T.object
  /*controlId: T.string.isRequired,
  onChange: T.func.isRequired*/
}

Color.defaultProps = {
  style: {}
}

export {
  Color
}
