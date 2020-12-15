import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'

const ContentForbidden = (props) =>
  <div className={classes('content-forbidden', props.className, {
    [`content-forbidden-${props.size}`]: !!props.size
  })}>
    <div className="content-forbidden-animation">
      <span className="fa fa-lock" />
    </div>

    {props.title || trans('forbidden')}

    {props.description &&
      <p className="content-forbidden-description">{props.description}</p>
    }

    {props.children}
  </div>

ContentForbidden.propTypes = {
  className: T.string,
  size: T.oneOf(['sm', 'lg']),
  title: T.string,
  description: T.string,
  children: T.node
}

export {
  ContentForbidden
}
