import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

const CountryFlag = (props) =>
  <span
    {...omit(props, 'countryCode', 'square')}
    className={classes(props.className, `fi fi-${props.countryCode ? props.countryCode.toLowerCase() : 'xx'}`, {
      'fis': props.square
    })}
    aria-hidden={true}
  />

CountryFlag.propTypes = {
  className: T.string,
  countryCode: T.string,
  square: T.bool
}

CountryFlag.defaultProps = {
  square: false
}

export {
  CountryFlag
}
