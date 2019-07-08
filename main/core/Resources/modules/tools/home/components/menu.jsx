import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {LINK_BUTTON} from '#/main/app/buttons'
import {MenuSection} from '#/main/app/layout/menu/components/section'

const HomeMenu = (props) =>
  <MenuSection
    {...omit(props, 'path', 'tabs')}
    title={trans('home', {}, 'tools')}
  >
    {0 < props.tabs.length &&
      <div className="list-group">
        {props.tabs.map(tab =>
          <Button
            key={tab.id}
            className="list-group-item"
            type={LINK_BUTTON}
            icon={tab.icon ? `fa fa-fw fa-${tab.icon}` : undefined}
            label={tab.title}
            target={`${props.path}/tab/${tab.id}`}
            activeStyle={{
              borderColor: get(tab, 'display.color')
            }}
          />
        )}
      </div>
    }
  </MenuSection>

HomeMenu.propTypes = {
  path: T.string,
  tabs: T.arrayOf(T.shape({
    // TODO : tab types
  })),

  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired
}

HomeMenu.defaultProps = {
  tabs: []
}

export {
  HomeMenu
}
