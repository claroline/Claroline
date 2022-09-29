import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

// todo : use in web resource bundle
const ContentIFrame = (props) =>
  <div
    className={classes('content-iframe-container', props.className)}
    style={props.ratio ? {
      position: 'relative',
      paddingBottom: `${props.ratio}%`
    } : {}}
  >
    <iframe className="content-iframe" src={props.url} />
  </div>

ContentIFrame.propTypes = {
  className: T.string,
  url: T.string.isRequired,
  ratio: T.number
}

export {
  ContentIFrame
}
