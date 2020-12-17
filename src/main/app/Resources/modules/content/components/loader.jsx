import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {Hourglass} from '#/main/app/animation/components/hourglass'

const ContentLoader = (props) =>
  <div className={classes('content-loader', props.className, {
    [`content-loader-${props.size}`]: !!props.size
  })}>
    <div className="content-loader-animation">
      <Hourglass />
    </div>

    {trans('please_wait')}

    {props.description &&
      <p className="content-loader-description">{props.description}</p>
    }
  </div>

ContentLoader.propTypes = {
  className: T.string,
  size: T.oneOf(['sm', 'lg']),
  description: T.string
}

export {
  ContentLoader
}
