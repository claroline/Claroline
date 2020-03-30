import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {MENU_BUTTON} from '#/main/app/buttons'

const ContentTabs = (props) =>
  <ul className={classes('nav nav-tabs', props.className)}>
    {!isEmpty(props.backAction) &&
      <li className="nav-back">
        <Button
          label={trans('back')}
          {...props.backAction}
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
          className={classes({
            active: section.active
          })}
        >
          <Button
            {...section}
          />
        </li>
      )
    }

    {!isEmpty(props.actions) &&
      <li className="nav-actions">
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
  backAction: T.shape({
    // TODO : action types
  }),
  actions: T.arrayOf(T.shape({
    // TODO : action types
  })),
}

export {
  ContentTabs
}
