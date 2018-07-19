import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {Button} from '#/main/app/action/components/button'

import {Tab as TabTypes} from '#/main/core/tools/home/prop-types'

const Tabs = props =>
  <nav className="tool-nav">
    {props.tabs.map((tab) =>
      <Button
        key={tab.id}
        type="link"
        className="nav-tab"
        icon={tab.icon ? `fa fa-fw fa-${tab.icon}` : undefined}
        label={tab.title}
        target={`${props.prefix}/tab/${tab.id}`}
        exact={true}
      />
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
