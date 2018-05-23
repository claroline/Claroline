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
      strokeDasharray={props.strokeDasharray.join(', ')}
      d={props.line}
      fill="none"
    />
  </g>

Path.propTypes = {
  strokeColor: T.string.isRequired,
  line: T.string.isRequired,
  area: T.oneOfType([T.string, T.bool]).isRequired,
  strokeWidth: T.number,
  strokeDasharray: T.array
}

Path.defaultProps = {
  strokeWidth: 2,
  strokeDasharray: ['none'],
  line: '',
  area: ''
}

export {
  Path
}