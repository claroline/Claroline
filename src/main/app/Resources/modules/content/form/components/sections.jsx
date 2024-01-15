import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

import {Section, Sections} from '#/main/app/content/components/sections'
import {getValidationClassName} from '#/main/app/content/form/validator'

/**
 * Renders a form section.
 */
const FormSection = (props) =>
  <Section
    {...omit(props, 'validating', 'errors')}
    className={classes('form-section', props.className, getValidationClassName(props.errors, props.validating))}
  >
    {props.children}
  </Section>

FormSection.propTypes = {
  id: T.string,
  className: T.string,
  children: T.node.isRequired,
  disabled: T.bool,
  validating: T.bool,
  errors: T.oneOfType([T.object, T.array])
}

FormSection.defaultProps = {
  validating: false
}

const FormSections = props =>
  <Sections
    className={classes('form-sections', props.className)}
    level={props.level}
    displayLevel={props.displayLevel}
    accordion={props.accordion}
    defaultOpened={props.defaultOpened}
    flush={props.flush}
  >
    {props.children}
  </Sections>

FormSections.propTypes = {
  className: T.string,
  accordion: T.bool,
  flush: T.bool,
  level: T.number, // level for panel headings
  displayLevel: T.number, // modifier for headings level (used when some headings levels are hidden in the page)
  defaultOpened: T.string,
  children: T.node.isRequired
}

export {
  FormSection,
  FormSections
}
