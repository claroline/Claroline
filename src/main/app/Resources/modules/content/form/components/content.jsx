import React, {createElement, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {toKey} from '#/main/core/scaffolding/text'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {ContentTitle} from '#/main/app/content/components/title'
import {FormFieldset} from '#/main/app/content/form/components/fieldset'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'

import {constants} from '#/main/app/content/form/constants'
import {createFormDefinition} from '#/main/app/content/form/utils'
import {DataFormSection as DataFormSectionTypes} from '#/main/app/content/form/prop-types'

function getSectionId(section, formId = null) {
  let id = formId ? `${formId}-` : ''

  id += section.id || toKey(section.title)

  return id
}

function getSectionErrors(sectionFields = [], errors = {}) {
  let sectionErrors = []

  sectionFields.map(field => {
    if (get(errors, field.name)) {
      sectionErrors.push(get(errors, field.name))
    }

    if (field.linked) {
      sectionErrors = sectionErrors.concat(getSectionErrors(field.linked, errors))
    }
  })

  return sectionErrors
}

const FormContent = (props) => {
  const sections = createFormDefinition(props.mode, props.definition, props.locked, props.data)

  const primarySections = 1 === sections.length ? [sections[0]] : sections.filter(section => section.primary)
  const otherSections = 1 !== sections.length ? sections.filter(section => !section.primary) : []
  let openedSection = otherSections.find(section => section.defaultOpened)

  const disabled = typeof props.disabled === 'function' ? props.disabled(props.data) : props.disabled

  if (props.autoFocus && !isEmpty(primarySections) && !isEmpty(primarySections[0].fields)) {
    primarySections[0].fields[0].autoFocus = true
  }

  return (
    <>
      {primarySections.map((primarySection, index) =>
        <Fragment key={primarySection.id || toKey(primarySection.title)}>
          {0 !== index &&
            <hr className="mb-5 mt-4" aria-hidden={true} />
          }

          <section
            id={`${getSectionId(primarySection, props.id)}-section`}
            className={classes('form-primary-section', primarySection.className/*, !props.flush && 'mb-3'*/)}
          >
            <ContentTitle
              className="mb-5"
              level={props.level}
              displayLevel={props.displayLevel}
              displayed={0 !== index && !primarySection.hideTitle}
              title={primarySection.title}
              subtitle={primarySection.subtitle}
            />

            {!isEmpty(primarySection.actions) &&
              <Toolbar
                id={`${getSectionId(primarySection, props.id)}-actions`}
                buttonName="btn"
                className="text-right form-group"
                size="sm"
                actions={primarySection.actions}
              />
            }

            <FormFieldset
              id={getSectionId(primarySection, props.id)}
              mode={props.mode}
              disabled={disabled || primarySection.disabled}
              fields={primarySection.fields}
              data={props.data}
              errors={props.errors}
              help={primarySection.help}
              validating={props.validating}
              updateProp={props.updateProp}
              setErrors={props.setErrors}
            >
              {primarySection.component && createElement(primarySection.component)}
              {!primarySection.component && primarySection.render && primarySection.render(props.data, props.errors)}
            </FormFieldset>
          </section>
        </Fragment>
      )}

      {0 !== otherSections.length &&
        <FormSections
          level={props.level}
          displayLevel={props.displayLevel}
          defaultOpened={openedSection ? getSectionId(openedSection, props.id) : undefined}
          flush={props.flush}
          /*className={classes(!props.flush && 'mb-3')}*/
        >
          {otherSections.map(section => (
            <FormSection
              id={getSectionId(section, props.id)}
              className={section.className}
              key={getSectionId(section, props.id)}
              icon={section.icon}
              title={section.title}
              subtitle={section.subtitle}
              errors={getSectionErrors(section.fields, props.errors)}
              validating={props.validating}
              actions={section.actions}
              fill={section.fill}
            >
              <FormFieldset
                id={`${getSectionId(section, props.id)}-fieldset`}
                mode={props.mode}
                disabled={disabled || (typeof section.disabled === 'function' ? section.disabled(props.data) : section.disabled)}
                fields={section.fields}
                data={props.data}
                errors={props.errors}
                help={section.help}
                validating={props.validating}
                updateProp={props.updateProp}
                setErrors={props.setErrors}
              >
                {section.component && createElement(section.component)}
                {!section.component && section.render && section.render(props.data, props.errors)}
              </FormFieldset>
            </FormSection>
          ))}
        </FormSections>
      }
    </>
  )
}

FormContent.propTypes = {
  id: T.string.isRequired,
  level: T.number,
  displayLevel: T.number,
  flush: T.bool,
  autoFocus: T.bool,
  mode: T.string.isRequired,
  disabled: T.oneOfType([T.bool, T.func]),

  definition: T.arrayOf(T.shape(
    DataFormSectionTypes.propTypes
  )).isRequired,
  locked: T.arrayOf(T.string), // a list of inputs to be locked in form

  errors: T.object,
  validating: T.bool,
  data: T.object,

  setErrors: T.func.isRequired,
  updateProp: T.func.isRequired
}

FormContent.defaultProps = {
  level: 2,
  disabled: false,
  flush: false,
  //autoFocus: true,
  mode: constants.FORM_MODE_DEFAULT,
  data: {}
}

export {
  FormContent
}
