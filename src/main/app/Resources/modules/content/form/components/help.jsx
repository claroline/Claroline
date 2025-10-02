import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {toKey} from '#/main/app/utils/text'

const FormHelp = (props) => {
  const helps = Array.isArray(props.help) && 1 === props.help.length ? props.help[0] : props.help

  if (Array.isArray(helps)) {
    return (
      <div className={classes('list-unstyled', props.className)} role="presentation">
        {helps.map(help =>
          <p key={toKey(help)} className="form-text">
            {help}
          </p>
        )}
      </div>
    )
  }

  return (
    <p className={classes('form-text', props.className)}>
      {helps}
    </p>
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
