import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {ContentTitle} from '#/main/app/content/components/title'
import {ContentTabs} from '#/main/app/content/components/tabs'

/**
 * @deprecated
 */
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

    <ContentTabs
      backAction={props.backAction}
      sections={props.sections}
      actions={props.actions}
    />
  </header>

ContentHeading.propTypes = {
  className: T.string,
  level: T.number.isRequired,
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
