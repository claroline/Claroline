import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/core/translation'
import {Button} from '#/main/app/action/components/button'
import {LinkButton} from '#/main/app/button/components/link'

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
        {tab.locked &&
          <span className="fa fa-fw fa-lock icon-with-text-left" />
        }
      </LinkButton>
    )}

    {props.create &&
      <Button
        className="nav-add-tab"
        type="callback"
        icon="fa fa-fw fa-plus"
        label={trans('add_tab', {}, 'home')}
        tooltip="bottom"
        callback={props.create}
      />
    }
  </nav>

Tabs.propTypes = {
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
