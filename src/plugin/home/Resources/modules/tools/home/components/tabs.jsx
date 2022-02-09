import React, {useEffect, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import {get, isEmpty} from 'lodash'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {LinkButton} from '#/main/app/buttons/link/components/button'

import {Tab as TabTypes} from '#/plugin/home/prop-types'

const Tab = ({tab, prefix, closeTab, isChild = false}) =>
  <LinkButton
    key={tab.id}
    className={classes('nav-tab', {
      'nav-tab-hidden': get(tab, 'restrictions.hidden'),
      'top-tab': !isChild,
      'dropdown-tab': isChild
    })}
    target={`${prefix}/${tab.slug}`}
    activeStyle={{
      backgroundColor: get(tab, 'display.color'),
      borderColor: get(tab, 'display.color')
    }}
    onClick={closeTab}
  >
    {tab.icon &&
      <span className={classes('fa fa-fw', `fa-${tab.icon}`, tab.title && 'icon-with-text-right')} />
    }
    {tab.title}
  </LinkButton>

const Tabs = props => {
  const [expandedTab, setExpandedTab] = useState('')
  useEffect(() => {
    const previousWindowOnClick = window.onclick

    window.onclick = ({target}) => !target.matches('top-tabs') && setExpandedTab('')

    return () => {
      window.onclick = previousWindowOnClick
    }
  }, [])

  const toggleTab = tab => {
    const newState = tab.id === expandedTab ? '' : tab.id
    setExpandedTab(newState)
  }
  const isTabExpanded = tab => tab.id === expandedTab

  const getSubtabs = (subtabs) =>
    <ul className="dropdown-tabs">
      {subtabs.filter(subTab => props.showHidden || !get(subTab, 'restrictions.hidden', false)).map(subtab =>
        <li className="dropdown-tab-item" key={subtab.id}>
          <Tab tab={subtab} prefix={props.prefix} isChild={true} closeTab={() => setExpandedTab('')} />
        </li>
      )}
    </ul>

  return <nav className="tool-nav">
    <ul className="top-tabs">
      {props.tabs
        .filter(tab => props.showHidden || !get(tab, 'restrictions.hidden', false))
        .map((tab) => {
          const canShowSubTabs = !isEmpty(tab.children) && props.showSubMenu

          return <li className={classes('top-tab-item', {'dropdown': canShowSubTabs})} key={tab.id}>
            <div className="top-item">
              <Tab tab={tab} prefix={props.prefix} closeTab={() => setExpandedTab('')} />
              {canShowSubTabs && <Button
                className="expand-sub-menu"
                type={CALLBACK_BUTTON}
                icon={classes('fa fa-fw', {
                  'fa-caret-up': isTabExpanded(tab),
                  'fa-caret-down': !isTabExpanded(tab)
                })}
                label=""
                callback={() => toggleTab(tab)}
              />}
            </div>
            {canShowSubTabs && isTabExpanded(tab) && getSubtabs(tab.children)}
          </li>
        })
      }
    </ul>

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
}

Tabs.propTypes = {
  showHidden: T.bool,
  showSubMenu: T.bool,
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
