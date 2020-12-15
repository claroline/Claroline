import React from 'react'
import {PropTypes as T} from 'prop-types'

const Path = (props) =>
  <g>
    {props.area !== false &&
      <path
        fill={props.strokeColor}
        d={props.area}
        style={{fillOpacity: 0.1}}
      />
    }

    <path
      stroke={props.strokeColor}
      strokeWidth={props.strokeWidth}
      strokeLinejoin="round"
      d={props.line}
      fill="none"
    />
  </g>

Path.propTypes = {
  strokeColor: T.string.isRequired,
  line: T.string.isRequired,
  area: T.oneOfType([T.string, T.bool]).isRequired,
  strokeWidth: T.number
}

Path.defaultProps = {
  strokeWidth: 2,
  line: '',
  area: ''
}

export {
  Path
}