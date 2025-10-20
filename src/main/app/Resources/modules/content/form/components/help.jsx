import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {toKey} from '#/main/app/utils/text'
import {Html} from '#/main/app/components/html'

const FormHelp = (props) => {
  const helps = Array.isArray(props.help) && 1 === props.help.length ? props.help[0] : props.help

  if (Array.isArray(helps)) {
    return (
      <div className={props.className} role="presentation">
        {helps.map(help =>
          <Html as="p" key={toKey(help)} className="form-text mb-0">
            {help}
          </Html>
        )}
      </div>
    )
  }

  return (
    <Html as="p" className={classes('form-text', props.className)}>
      {helps}
    </Html>
  )
}

FormHelp.propTypes = {
  className: T.string,
  help: T.oneOfType([
    T.string,           // a single help message
    T.arrayOf(T.string) // a list of help messages
  ]).isRequired
}

export {
  FormHelp
}
