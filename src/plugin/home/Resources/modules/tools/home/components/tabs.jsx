import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import tinycolor from 'tinycolor2'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl'
import {Button} from '#/main/app/action/components/button'
import {MENU_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {LinkButton} from '#/main/app/buttons/link/components/button'

import {Tab as TabTypes} from '#/plugin/home/prop-types'

const Tab = ({tab, prefix}) =>
  <LinkButton
    key={tab.id}
    className="home-nav-link nav-link"
    target={`${prefix}/${tab.slug}`}
  >
    {tab.icon &&
      <span className={classes('fa fa-fw', `fa-${tab.icon}`, tab.title && 'icon-with-text-right')} />
    }
    {tab.title}
  </LinkButton>

const HomeTabs = props =>
  <nav className="page-nav home-nav">
    <ul className="nav nav-underline">
      {props.tabs
        .filter(tab => props.showHidden || !get(tab, 'restrictions.hidden', false))
        .map((tab) => {
          const children = get(tab, 'children', [])
            .filter(subTab => props.showHidden || !get(subTab, 'restrictions.hidden', false))
          const canShowSubTabs = !isEmpty(children) && props.showSubMenu

          let color
          if (get(tab, 'display.color')) {
            color = tinycolor(get(tab, 'display.color'))
          }

          return (
            <li
              key={tab.id}
              className={classes('home-nav-item nav-item', canShowSubTabs && 'btn-group', {
                'home-nav-item-hidden': get(tab, 'restrictions.hidden')
              }, props.currentTabId === tab.id && {
                'active': props.currentTabId === tab.id,
                'text-light': color && color.isDark(),
                'text-dark': color && color.isLight()
              })}
              style={props.currentTabId === tab.id ? {
                backgroundColor: get(tab, 'display.color'),
                borderColor: get(tab, 'display.color')
              } : undefined}
            >
              <Tab tab={tab} prefix={props.prefix} />
              {canShowSubTabs &&
                <Button
                  className="home-nav-expand"
                  type={MENU_BUTTON}
                  icon="fa fa-fw fa-caret-down"
                  label={trans('show_sub_tabs')}
                  tooltip="bottom"
                  menu={{
                    align: 'right',
                    items: children.map(subTab => {
                      let childColor
                      if (get(subTab, 'display.color')) {
                        childColor = tinycolor(get(subTab, 'display.color'))
                      }

                      return {
                        type: LINK_BUTTON,
                        className: classes(props.currentTabId === subTab.id && {
                          'text-light': childColor && childColor.isDark(),
                          'text-dark': childColor && childColor.isLight()
                        }),
                        target: `${props.prefix}/${subTab.slug}`,
                        icon: subTab.icon ? `fa fa-fw fa-${subTab.icon}` : undefined,
                        label: subTab.title,
                        activeStyle: props.currentTabId === subTab.id && {
                          backgroundColor: get(subTab, 'display.color'),
                          borderColor: get(subTab, 'display.color')
                        }
                      }
                    })
                  }}
                />
              }
            </li>
          )
        })
      }
    </ul>
  </nav>

HomeTabs.propTypes = {
  prefix: T.string,
  tabs: T.arrayOf(T.shape(
    TabTypes.propTypes
  )),
  currentTabId: T.string,
  showHidden: T.bool,
  showSubMenu: T.bool
}

HomeTabs.defaultProps = {
  prefix: ''
}

export {
  HomeTabs
}
