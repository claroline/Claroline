import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {Hourglass} from '#/main/app/animation/components/hourglass'

const ContentLoader = (props) =>
  <div className={classes('content-loader', {
    [`content-loader-${props.size}`]: !!props.size,
    [`content-loader-${props.direction}`]: !!props.direction
  })}>
    <div className="content-loader-animation">
      <Hourglass />
    </div>

    Merci de patienter quelques instants
  </div>

ContentLoader.propTypes = {
  size: T.oneOf(['sm', 'lg']),
  direction: T.oneOf(['horizontal', 'vertical'])
}

export {
  ContentLoader
}
