import React, {useId} from 'react'
import {PropTypes as T} from 'prop-types'
import {Collapse} from 'react-bootstrap'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'
import omit from 'lodash/omit'

import {Section, Sections} from '#/main/app/content/components/sections'
import {Heading} from '#/main/app/components/heading'
import {Toolbar} from '#/main/app/action'

import {getValidationClassName} from '#/main/app/content/form/validator'

const FormPrimarySection = ({
  className,
  title,
  description,
  children,
  icon = null,
  level = 2,
  displayLevel = 5,
  hideTitle = false,
  actions = []
}) => {
  const titleId = useId()
  const descriptionId = useId()

  return (
    <section
      className={classes('data-form-section form-primary-section', className)}
      aria-labelledby={title ? titleId : undefined}
      aria-describedby={description ? descriptionId : undefined}
    >
      {title &&
        <header className={classes({
          'visually-hidden': hideTitle
        })}>
          <Heading
            id={titleId}
            className="mb-0"
            level={level}
            displayLevel={displayLevel}
          >
            {icon &&
              <span className={classes('h5 my-auto me-2', icon)} aria-hidden={true} />
            }

            {title}
          </Heading>

          {description &&
            <p id={descriptionId} className="text-body-secondary mt-2 mb-0">{description}</p>
          }
        </header>
      }

      {!isEmpty(actions) &&
        <Toolbar
          buttonName="btn btn-body"
          className="text-right form-group"
          size="sm"
          actions={actions}
        />
      }

      {children}
    </section>
  )
}

FormPrimarySection.propTypes = {
  className: T.string,
  level: T.number, // level for section heading
  displayLevel: T.number, // modifier for heading level (used when some heading levels are hidden in the page)
  icon: T.string,
  title: T.string,
  hideTitle: T.bool,
  description: T.string,
  actions: T.array,
  children: T.node.isRequired
}

const FormToggleSection = (props) => {
  const titleId = useId()
  const descriptionId = useId()
  const toggleId = useId()

  return (
    <section
      className={classes('data-form-section form-primary-section', props.className)}
      aria-labelledby={titleId}
      aria-describedby={props.description ? descriptionId : undefined}
    >
      <header>
        <Heading
          id={titleId}
          className="mb-0 form-check form-switch form-check-reverse d-flex flex-row flex-nowrap align-items-center"
          level={props.level}
          displayLevel={props.displayLevel - 2}
        >
          {props.icon &&
            <span className={classes('h5 my-auto me-2', props.icon, {'text-body-secondary': !props.displayed})} aria-hidden={true} />
          }

          <label
            className={classes('form-check-label flex-fill text-start', {
              'text-body-secondary': !props.displayed
            }, `fs-${props.displayLevel}`)}
            htmlFor={toggleId}
          >
            {props.title}
          </label>

          <input
            id={toggleId}
            className="form-check-input"
            type="checkbox"
            checked={props.displayed}
            disabled={props.disabled}
            onChange={(e) => props.onToggle(e.target.checked)}
            role="switch"
          />
        </Heading>

        {props.description &&
          <p id={descriptionId} className="text-body-secondary mt-2 mb-0">{props.description}</p>
        }
      </header>

      <Collapse in={props.displayed} appear={false}>
        <div role="presentation">
          {!isEmpty(props.actions) &&
            <Toolbar
              buttonName="btn"
              className="text-right form-group"
              size="sm"
              actions={props.actions}
            />
          }

          {props.children}
        </div>
      </Collapse>
    </section>
  )
}

FormToggleSection.propTypes = {
  className: T.string,
  level: T.number, // level for section heading
  displayLevel: T.number, // modifier for heading level (used when some heading levels are hidden in the page)
  icon: T.string,
  title: T.string,
  hideTitle: T.bool,
  description: T.string,
  actions: T.array,
  children: T.node.isRequired,
  displayed: T.bool,
  onToggle: T.func.isRequired,
  disabled: T.bool
}

/**
 * Renders a form section.
 */
const FormSection = (props) =>
  <Section
    {...omit(props, 'errors')}
    className={classes(props.className, getValidationClassName(props.errors))}
  >
    {props.children}
  </Section>

FormSection.propTypes = {
  id: T.string,
  className: T.string,
  children: T.node.isRequired,
  disabled: T.bool,
  errors: T.oneOfType([T.object, T.array])
}

const FormSections = props =>
  <Sections
    className={classes('data-form-sections', props.className)}
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
  FormPrimarySection,
  FormToggleSection,
  FormSection,
  FormSections
}
