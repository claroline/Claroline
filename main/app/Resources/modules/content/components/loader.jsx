import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {Hourglass} from '#/main/app/animation/components/hourglass'

const ContentLoader = (props) =>
  <div className={classes('content-loader', {
    [`content-loader-${props.size}`]: !!props.size,
    [`content-loader-${props.direction}`]: !!props.direction
  })}>
    <div className="content-loader-animation">
      <Hourglass />
    </div>

    {trans('please_wait')}

    {props.description &&
      <p>{props.description}</p>
    }
  </div>

ContentLoader.propTypes = {
  size: T.oneOf(['sm', 'lg']),
  direction: T.oneOf(['horizontal', 'vertical']),
  description: T.string
}

export {
  ContentLoader
}
