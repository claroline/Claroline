import React, {useEffect, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {asset} from '#/main/app/config'
import {icon} from '#/main/theme/config'
import {makeCancelable} from '#/main/app/api'

const cacheIcons = {}

/**
 * @internal
 */
const ThemeUrlIcon = (props) => {
  if (props.svg) {
    const url = asset(props.url)
    const [svg, setSvg] = useState(cacheIcons[url] || null)

    // Weird things happen here.
    // I manually load the SVG because I need to directly mount the SVG into the DOM.
    // I do it because I want to be able to style SVG content through theme, and it's only possible with inline SVG or SVG sprites.
    // Doing it like this should keep the browser cache system working
    // ATTENTION: the wrapping span is required because of the DOM manipulation
    useEffect(() => {
      if (!cacheIcons[url]) {
        const iconFetching = makeCancelable(fetch(url, {
          credentials: 'include'
        }))

        iconFetching.promise
          .then(response => response.text())
          .then(response => {
            cacheIcons[url] = response
            setSvg(response)
          })

        return () => iconFetching.cancel()
      }
    })

    return (
      <span
        role="presentation"
        className={classes(props.className, 'theme-icon', props.size && `theme-icon-${props.size}`)}
        dangerouslySetInnerHTML={{ __html: svg }}
      />
    )
  }

  return (
    <span role="presentation" className={classes(props.className, 'theme-icon', props.size && `theme-icon-${props.size}`)}>
      <img src={asset(props.url)} />
    </span>
  )
}

ThemeUrlIcon.propTypes = {
  className: T.string,
  url: T.string.isRequired,
  svg: T.bool.isRequired,
  size: T.oneOf(['xs', 'sm', 'md', 'lg', 'xl'])
}

const ThemeIcon = props => {
  const iconInfo = icon(props.mimeType, props.set)

  return (
    <ThemeUrlIcon
      className={props.className}
      url={iconInfo.url}
      svg={iconInfo.svg}
      size={props.size}
    />
  )
}

ThemeIcon.propTypes = {
  className: T.string,
  mimeType: T.string,
  set: T.oneOf(['resources', 'widgets', 'data']),
  size: T.oneOf(['xs', 'sm', 'md', 'lg', 'xl'])
}

export {
  ThemeIcon,
  // For internal use only, this is used in the icons preview
  // MUST not be used by implementations !!
  ThemeUrlIcon
}
