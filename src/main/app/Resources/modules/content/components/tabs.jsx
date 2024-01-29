import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {MENU_BUTTON} from '#/main/app/buttons'

const ContentTabs = (props) =>
  <ul className={classes('nav nav-underline border-bottom', props.className)}>
    {!isEmpty(props.backAction) &&
      <li className="nav-item">
        <Button
          label={trans('back')}
          {...props.backAction}
          className="nav-link"
          icon="fa fa-fw fa-arrow-left"
          tooltip="bottom"
        />
      </li>
    }

    {props.sections
      .filter(section => undefined === section.displayed || section.displayed)
      .map(section =>
        <li
          key={section.name}
          className="nav-item"
        >
          <Button
            {...section}
            className={classes('nav-link', {
              active: section.active
            })}
          />
        </li>
      )
    }

    {!isEmpty(props.actions) &&
      <li className="nav-item nav-actions">
        <Button
          type={MENU_BUTTON}
          icon="fa fa-fw fa-ellipsis-v"
          label={trans('show-more-actions', {}, 'actions')}
          tooltip="bottom"
          menu={{
            align: 'right',
            items: props.actions
          }}
        />
      </li>
    }
  </ul>

ContentTabs.propTypes = {
  className: T.string,
  sections: T.arrayOf(T.shape({
    // TODO : action types
  })),
  /**
   * @deprecated
   */
  backAction: T.object,
  /**
   * @deprecated
   */
  actions: T.arrayOf(T.object)
}

export {
  ContentTabs
}
