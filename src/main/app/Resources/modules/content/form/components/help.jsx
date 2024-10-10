import React from 'react'
import {PropTypes as T} from 'prop-types'

import {toKey} from '#/main/core/scaffolding/text'

const FormHelp = (props) => {
  const helps = Array.isArray(props.help) && 1 === props.help.length ? props.help[0] : props.help

  if (Array.isArray(helps)) {
    return (
      <ul className="list-unstyled mb-0">
        {helps.map(help =>
          <li key={toKey(help)} className="form-text">
            {help}
          </li>
        )}
      </ul>
    )
  }

  return (
    <div className="form-text">
      {helps}
    </div>
  )
}

FormHelp.propTypes = {
  help: T.oneOfType([
    T.string,           // a single help message
    T.arrayOf(T.string) // a list of help messages
  ]).isRequired
}

export {
  FormHelp
}
