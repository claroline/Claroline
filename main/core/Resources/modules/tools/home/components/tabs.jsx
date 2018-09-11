import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/core/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {LinkButton} from '#/main/app/buttons/link/components/button'

import {Tab as TabTypes} from '#/main/core/tools/home/prop-types'

const Tabs = props =>
  <nav className="tool-nav">
    {props.tabs.map((tab) =>
      <LinkButton
        key={tab.id}
        className="nav-tab"
        target={`${props.prefix}/tab/${tab.id}`}
        exact={true}
      >
        {tab.icon &&
        <span className={classes('fa fa-fw', `fa-${tab.icon}`, tab.title && 'icon-with-text-right')} />
        }
        {tab.title}
        {!tab.visible && props.editing &&
          <span className="fa fa-fw fa-eye-slash icon-with-text-left" />
        }
      </LinkButton>
    )}

    {props.create &&
      <Button
        className="nav-add-tab"
        type={CALLBACK_BUTTON}
        icon="fa fa-fw fa-plus"
        label={trans('add_tab', {}, 'home')}
        tooltip="bottom"
        callback={props.create}
      />
    }
  </nav>

Tabs.propTypes = {
  context: T.object.isRequired,
  editing: T.bool.isRequired,
  prefix: T.string,
  tabs: T.arrayOf(T.shape(
    TabTypes.propTypes
  )),
  create: T.func
}

Tabs.defaultProps = {
  prefix: ''
}

export {
  Tabs
}
