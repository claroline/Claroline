import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

const MenuSection = props =>
  <nav className={classes('app-menu-section', props.className, {
    opened: props.opened
  })}>
    <h2 className="h4">
      <Button
        type={CALLBACK_BUTTON}
        icon={props.icon}
        label={props.title}
        callback={props.toggle}
      />
    </h2>

    {props.opened && props.children}
  </nav>

MenuSection.propTypes = {
  className: T.string,
  icon: T.string,
  title: T.string.isRequired,
  opened: T.bool.isRequired,
  toggle: T.func.isRequired,
  children: T.element
}

export {
  MenuSection
}