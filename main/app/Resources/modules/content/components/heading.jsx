import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {MENU_BUTTON} from '#/main/app/buttons'
import {ContentTitle} from '#/main/app/content/components/title'

const ContentHeading = props =>
  <header className={classes('content-heading', props.className)}>
    {props.image &&
      <div className="content-heading-image img-thumbnail">
        {props.image}
      </div>
    }

    <div className="content-heading-info">
      <ContentTitle
        className="content-heading-title"
        level={props.level}
        displayLevel={props.displayLevel}
        title={props.title}
        subtitle={props.subtitle}
      />

      {props.children}
    </div>

    <ul className="nav nav-tabs">
      {!isEmpty(props.backAction) &&
        <li className="nav-back">
          <Button
            {...props.backAction}
          />
        </li>
      }

      {props.sections.map(section =>
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
      )}

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
  </header>

ContentHeading.propTypes = {
  className: T.string,
  level: T.number.isRequired, // TODO : implement
  displayLevel: T.number,
  image: T.node,
  title: T.string.isRequired,
  subtitle: T.string,
  sections: T.arrayOf(T.shape({
    // TODO : action types
  })),
  backAction: T.shape({
    // TODO : action types
  }),
  actions: T.arrayOf(T.shape({
    // TODO : action types
  })),
  children: T.any
}

ContentHeading.defaultProps = {
  sections: [],
  actions: []
}

export {
  ContentHeading
}
