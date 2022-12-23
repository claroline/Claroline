import React, {useRef} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

import {asset} from '#/main/app/config'
import {icon} from '#/main/theme/config'

const cacheIcons = {}

/**
 * @internal
 */
const ThemeUrlIcon = (props) => {
  if (props.svg) {
    const url = asset(props.url)
    if (cacheIcons[url]) {
      // reuse cache if any to avoid the DOM replacement after the first load (avoid blinking)

      let className = classes('theme-icon', props.className)

      // grab original HTML attributes
      // we remove invalid `xml:*` and style first
      const originalAttr = cacheIcons[url].getAttributeNames()
        .filter(attr => 'style' !== attr && !attr.includes(':'))
        .reduce((attrs, current) => {
          if ('class' === current) {
            className = classes(className, cacheIcons[url].getAttribute(current))
          }

          return Object.assign(attrs, {
            [current]: cacheIcons[url].getAttribute(current)
          })
        }, {})

      return (
        <span role="presentation" className={className}>
          <svg
            dangerouslySetInnerHTML={{ __html: cacheIcons[url].innerHTML }}
            {...omit(originalAttr, 'class')}
          />
        </span>
      )
    }

    const objectEl = useRef(null)

    // Weird things happen here.
    // I let the object tag load the svg file content and then replace <object> by the loaded SVG XML.
    // I do it because I want to be able to style SVG content through theme and it's only possible with inline SVG or SVG sprites.
    // Doing it like this should keep the browser cache system working
    // ATTENTION: the wrapping span is required because of the DOM manipulation
    return (
      <span role="presentation" ref={objectEl} className={classes('theme-icon', props.className)}>
        <object
          type="image/svg+xml"
          data={url}
          onLoad={(event) => {
            // get the loaded XML
            const svgElement = event.currentTarget.contentDocument.documentElement

            if (objectEl.current.contains(event.target)) {
              // directly mount the SVG into the DOM and remove the original <object> tag from the DOM
              objectEl.current.replaceChild(svgElement, event.target)

              // cache result to avoid the DOM replacement after the first load (avoid blinking)
              cacheIcons[url] = svgElement
            }
          }}
        />
      </span>
    )
  }

  return (
    <span role="presentation" className={classes('theme-icon', props.className)}>
      <img src={asset(props.url)} />
    </span>
  )
}

ThemeUrlIcon.propTypes = {
  className: T.string,
  url: T.string.isRequired,
  svg: T.bool.isRequired
}

const ThemeIcon = props => {
  const iconInfo = icon(props.mimeType, props.set)

  return (
    <ThemeUrlIcon
      className={props.className}
      url={iconInfo.url}
      svg={iconInfo.svg}
    />
  )
}

ThemeIcon.propTypes = {
  className: T.string,
  mimeType: T.string.isRequired,
  set: T.oneOf(['resources', 'widgets', 'data'])
}

export {
  ThemeIcon,
  // For internal use only, this is used in the icons preview
  // MUST not be used by implementations !!
  ThemeUrlIcon
}
