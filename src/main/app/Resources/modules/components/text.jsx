import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'
import range from 'lodash/range'

import {nl2br as nl2brFn} from '#/main/app/utils/text'

const TextSkeleton = ({
  className,
  rows = 5
}) => {
  return (
    <p className={classes('placeholder-glow', className)}>
      {range(0, rows).map(row =>
        <span key={row} className={classes('placeholder rounded-1', {
          'w-100': row !== rows - 1,
          'w-25': row === rows - 1
        })} />
      )}
    </p>
  )
}

TextSkeleton.propTypes = {
  className: T.string,
  rows: T.number
}

/**
 * Displays a multiline text content.
 */
const Text = ({
  children,
  className,
  align = 'start',
  nl2br = false
}) => {
  if (isEmpty(children)) {
    return null
  }

  let content = children
  if (nl2br) {
    content = nl2brFn(children)
  }

  return (
    <p
      className={classes(`text-${align}`, className)}
      dangerouslySetInnerHTML={{ __html: content }}
    />
  )
}

Text.propTypes = {
  /**
   * HTML content to display.
   */
  children: T.string,

  /**
   * Additional classes to add to the DOM.
   */
  className: T.string,
  align: T.oneOf(['start', 'center', 'end', 'justify']),
  nl2br: T.bool
}

export {
  Text,
  TextSkeleton
}
