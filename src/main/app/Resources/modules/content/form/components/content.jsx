import React, {createElement, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

import {FormFieldset} from '#/main/app/content/form/components/fieldset'
import {
  FormSections,
  FormSection,
  FormPrimarySection,
  FormToggleSection
} from '#/main/app/content/form/components/sections'

import {createFormDefinition, getSectionId, getSectionErrors} from '#/main/app/content/form/utils'
import {DataFormSection as DataFormSectionTypes} from '#/main/app/content/form/prop-types'

const FormContent = (props) => {
  const sections = createFormDefinition(props.definition, props.locked, props.data)

  const primarySections = 1 === sections.length ? [sections[0]] : sections.filter(section => section.primary)
  const otherSections = 1 !== sections.length ? sections.filter(section => !section.primary) : []
  let openedSection = otherSections.find(section => section.defaultOpened)

  const disabled = typeof props.disabled === 'function' ? props.disabled(props.data) : props.disabled

  if (props.autoFocus && !isEmpty(primarySections) && !isEmpty(primarySections[0].fields)) {
    primarySections[0].fields[0].autoFocus = true
  }

  return (
    <div className={classes('data-form-content d-flex flex-column gap-5', props.className)} role="presentation">
      {primarySections.map((primarySection, index) =>
        <Fragment key={primarySection.title}>
          {0 !== index &&
            <hr className="my-0" aria-hidden={true} />
          }

          {!primarySection.onToggle ?
            <FormPrimarySection
              level={props.level}
              displayLevel={props.displayLevel}
              className={primarySection.className}
              title={primarySection.title}
              hideTitle={(0 === index && undefined === primarySection.hideTitle) || primarySection.hideTitle}
              description={primarySection.description}
              actions={primarySection.actions}
            >
              {!isEmpty(primarySection.fields) &&
                <FormFieldset
                  id={getSectionId(primarySection, props.id)}
                  disabled={disabled || (typeof primarySection.disabled === 'function' ? primarySection.disabled(props.data) : primarySection.disabled)}
                  fields={primarySection.fields}
                  data={props.data}
                  errors={props.errors}
                  help={primarySection.help}
                  updateProp={props.updateProp}
                  setErrors={props.setErrors}
                  size={props.size}
                />
              }
              {primarySection.component && createElement(primarySection.component)}
              {!primarySection.component && primarySection.render && primarySection.render(props.data, props.errors)}
            </FormPrimarySection> :
            <FormToggleSection
              level={props.level}
              displayLevel={props.displayLevel}
              className={primarySection.className}
              title={primarySection.title}
              description={primarySection.description}
              actions={primarySection.actions}
              disabled={disabled || (typeof primarySection.disabled === 'function' ? primarySection.disabled(props.data) : primarySection.disabled)}
              displayed={typeof primarySection.enabled === 'function' ? !!primarySection.enabled(props.data) : primarySection.enabled}
              onToggle={primarySection.onToggle}
            >
              {!isEmpty(primarySection.fields) &&
                <FormFieldset
                  id={getSectionId(primarySection, props.id)}
                  disabled={disabled || (typeof primarySection.disabled === 'function' ? primarySection.disabled(props.data) : primarySection.disabled)}
                  fields={primarySection.fields}
                  data={props.data}
                  errors={props.errors}
                  help={primarySection.help}
                  updateProp={props.updateProp}
                  setErrors={props.setErrors}
                  size={props.size}
                />
              }
              {primarySection.component && createElement(primarySection.component)}
              {!primarySection.component && primarySection.render && primarySection.render(props.data, props.errors)}
            </FormToggleSection>
          }
        </Fragment>
      )}

      {0 !== otherSections.length &&
        <FormSections
          level={props.level}
          displayLevel={props.displayLevel}
          defaultOpened={openedSection ? getSectionId(openedSection, props.id) : undefined}
          flush={props.flush}
        >
          {otherSections.map(section => (
            <FormSection
              id={getSectionId(section, props.id)}
              className={section.className}
              key={getSectionId(section, props.id)}
              icon={section.icon}
              title={section.title}
              subtitle={section.description}
              errors={getSectionErrors(section.fields, props.errors)}
              actions={section.actions}
            >
              {!isEmpty(section.fields) &&
                <FormFieldset
                  id={`${getSectionId(section, props.id)}-fieldset`}
                  disabled={disabled || (typeof section.disabled === 'function' ? section.disabled(props.data) : section.disabled)}
                  fields={section.fields}
                  data={props.data}
                  errors={props.errors}
                  help={section.help}
                  updateProp={props.updateProp}
                  setErrors={props.setErrors}
                  size={props.size}
                />
              }

              {section.component && createElement(section.component)}
              {!section.component && section.render && section.render(props.data, props.errors)}
            </FormSection>
          ))}
        </FormSections>
      }

      {props.children}
    </div>
  )
}

FormContent.propTypes = {
  id: T.string.isRequired,
  className: T.string,
  level: T.number,
  displayLevel: T.number,
  flush: T.bool,
  autoFocus: T.bool,
  size: T.string,
  disabled: T.oneOfType([T.bool, T.func]),
  children: T.any,
  definition: T.arrayOf(T.shape(
    DataFormSectionTypes.propTypes
  )).isRequired,
  locked: T.arrayOf(T.string), // a list of inputs to be locked in form

  errors: T.object,
  data: T.object,

  setErrors: T.func.isRequired,
  updateProp: T.func.isRequired
}

FormContent.defaultProps = {
  level: 2,
  displayLevel: 5,
  disabled: false,
  flush: false,
  data: {}
}

export {
  FormContent
}
