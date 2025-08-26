import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {Compass} from '#/main/app/animation/components/compass'

const ContentNotFound = (props) =>
  <div className={classes('content-not-found', props.className, {
    [`content-not-found-${props.size}`]: !!props.size
  })}>
    <div className="content-not-found-animation">
      <Compass />
    </div>

    <h2 className="h4 text-body-secondary mb-0">{props.title || trans('not_found')}</h2>

    {props.description &&
      <p className="text-body-tertiary mt-2 mb-0">{props.description}</p>
    }

    {props.children}
  </div>

ContentNotFound.propTypes = {
  className: T.string,
  size: T.oneOf(['sm', 'lg']),
  title: T.string,
  description: T.string,
  children: T.node
}

export {
  ContentNotFound
}
