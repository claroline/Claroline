import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

const Textarea = (props) => {
  // the data attribute on the container is used by a CSS trick to make the height of the textarea fit its content
  return (
    <div className={classes('textarea-container', {
      [`textarea-container-${props.size}`]: !!props.size,
      'textarea-resize-container': props.autoResize
    })} data-textarea-content={props.value || ''}>
      <textarea
        {...props}
        className={classes('form-control', props.className, {
          [`form-control-${props.size}`]: !!props.size,
          'scroller-thin': !props.autoResize
        })}
        rows={props.minRows}
        onChange={(e) => props.onChange(e.target.value)}
      />
    </div>
  )
}

Textarea.propTypes = {
  id: T.string.isRequired,
  className: T.string,
  size: T.oneOf(['sm', 'lg']),
  value: T.string,
  style: T.object,
  placeholder: T.string,
  autoComplete: T.string,
  autoFocus: T.bool,
  disabled: T.bool,
  minRows: T.number,
  autoResize: T.bool,
  onChange: T.func.isRequired
}

export {
  Textarea
}
