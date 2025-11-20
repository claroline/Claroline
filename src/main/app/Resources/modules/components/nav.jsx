import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {Button} from '#/main/app/action'
import isEmpty from 'lodash/isEmpty'
import {Badge} from '#/main/app/components/badge'

const Nav = (props) => {
  const displayedItems = props.items
    .filter(item => undefined === item.displayed || item.displayed)

  if (isEmpty(displayedItems)) {
    return null
  }

  return (
    <nav className={props.className} role={props.role}>
      <ul className={classes('nav', props.variant && `nav-${props.variant}`, {
        'flex-column': 'vertical' === props.orientation
      })}>
        {displayedItems
          .map((item) =>
            <li className="nav-item" key={item.label}>
              <Button
                {...item}
                className={classes('nav-link', item.className, {
                  active: item.active
                })}
              >
                {(item.badge || 0 === item.badge) &&
                  <Badge className="ms-2" subtle={true} variant={item.active ? 'primary': 'secondary'}>{item.badge}</Badge>
                }
              </Button>
            </li>
          )
        }
      </ul>
    </nav>
  )
}

Nav.propTypes = {
  variant: T.oneOf(['pills', 'underline', 'tabs', 'bar']),
  orientation: T.oneOf(['vertical', 'horizontal']).isRequired,
  items: T.arrayOf(T.shape({
    // action types
  })),
  className: T.string,
  role: T.string
}

Nav.defaultProps = {
  items: []
}

export {
  Nav
}
