import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {Button} from '#/main/app/action/components/button'
import {LINK_BUTTON} from '#/main/app/buttons'

const EditorMenu = (props) => {
  if (0 < props.tabs.length) {
    return (
      <div className="list-group">
        {props.tabs.map(tab =>
          <Button
            key={tab.id}
            className="list-group-item"
            type={LINK_BUTTON}
            icon={tab.icon ? `fa fa-fw fa-${tab.icon}` : undefined}
            label={tab.title}
            target={`${props.path}/edit/${tab.slug}`}
            activeStyle={{
              borderColor: get(tab, 'display.color')
            }}
          />
        )}
      </div>
    )
  }

  return null
}

/*<Button
  className="list-group-item"
  type={CALLBACK_BUTTON}
  icon="fa fa-fw fa-plus"
  label={trans('add_tab', {}, 'home')}
  callback={props.create}
/>*/

EditorMenu.propTypes = {
  path: T.string,
  tabs: T.arrayOf(T.shape({
    // TODO : tab types
  }))
  //create: T.func.isRequired
}

EditorMenu.defaultProps = {
  tabs: []
}

export {
  EditorMenu
}
