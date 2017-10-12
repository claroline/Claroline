import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {Section, Sections} from '#/main/core/layout/components/sections.jsx'

/**
 * Renders a form section.
 *
 * @param props
 * @constructor
 */
const FormSection = props =>
  <Section
    {...omit(props, ['validating', 'errors'])}
  >
    {props.children}
  </Section>

FormSection.propTypes = {
  children: T.node.isRequired,
  validating: T.bool,
  errors: T.object
}

const FormSections = props =>
  <Sections
    accordion={props.accordion}
    defaultOpened={props.defaultOpened}
    defaultActiveKey={props.defaultOpened}
  >
    {props.children}
  </Sections>

FormSections.propTypes = {
  accordion: T.bool,
  level: T.number, // level for panel headings
  defaultOpened: T.string,
  children: T.node.isRequired
}

FormSections.defaultProps = {
  accordion: true,
  level: 5
}

export {
  FormSection,
  FormSections
}
