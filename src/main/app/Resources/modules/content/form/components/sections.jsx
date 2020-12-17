import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'
import omit from 'lodash/omit'

import {Section, Sections} from '#/main/app/content/components/sections'
import {FormStatus} from '#/main/app/content/form/components/status'

/**
 * Renders a form section.
 *
 * @param props
 * @constructor
 */
const FormSection = props =>
  <Section
    {...omit(props, 'validating', 'errors')}
    className={classes('form-section', props.className)}
    status={!isEmpty(props.errors) ?
      <FormStatus id={props.id} validating={props.validating} position="left" /> :
      undefined
    }
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
    className="form-sections"
    level={props.level}
    displayLevel={props.displayLevel}
    accordion={props.accordion}
    defaultOpened={props.defaultOpened}
  >
    {props.children}
  </Sections>

FormSections.propTypes = {
  accordion: T.bool,
  level: T.number, // level for panel headings
  displayLevel: T.number, // modifier for headings level (used when some headings levels are hidden in the page)
  defaultOpened: T.string,
  children: T.node.isRequired
}

export {
  FormSection,
  FormSections
}
