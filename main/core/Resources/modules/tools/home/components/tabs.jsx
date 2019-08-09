import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {LinkButton} from '#/main/app/buttons/link/components/button'

import {Tab as TabTypes} from '#/main/core/tools/home/prop-types'

const Tabs = props =>
  <nav className="tool-nav">
    {props.tabs.map((tab) =>
      <LinkButton
        key={tab.id}
        className={classes('nav-tab', {
          'nav-tab-hidden': props.editing && tab.restrictions && tab.restrictions.hidden
        })}
        target={`${props.prefix}/${tab.slug}`}
        exact={true}
        activeStyle={{
          backgroundColor: get(tab, 'display.color'),
          borderColor: get(tab, 'display.color')
        }}
      >
        {tab.icon &&
          <span className={classes('fa fa-fw', `fa-${tab.icon}`, tab.title && 'icon-with-text-right')} />
        }

        {tab.title}
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
