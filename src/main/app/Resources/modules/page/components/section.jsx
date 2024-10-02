import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {ContentSizing} from '#/main/app/content/components/sizing'

const PageSection = (props) =>
  <section className={classes('page-section d-flex', props.className)}>
    <ContentSizing className={classes('flex-fill', {
      'px-4': !props.flush
    })} size={props.size}>
      {props.title &&
        <h2 className="page-section-title h6 mb-3">{props.title}</h2>
      }
      {props.children}
    </ContentSizing>
  </section>

PageSection.propTypes = {
  flush: T.bool,
  className: T.string,
  size: T.oneOf(['sm', 'md', 'lg', 'full']),

  // title configuration
  //level: T.number,
  //displayLevel: T.number, // pass null to hide the title
  title: T.string,
  subtitle: T.string,

  // actions toolbar
  actions: T.arrayOf(T.shape({
    // action types
  })),
  children: T.node
}

PageSection.defaultProps = {
  flush: false,
  level: 2, // level 1 is taken by the page title
  displayLevel: 2
}

export {
  PageSection
}
