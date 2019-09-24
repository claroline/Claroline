import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {toKey} from '#/main/core/scaffolding/text'
import {Heading} from '#/main/core/layout/components/heading'
import {ContentMeta} from '#/main/app/content/meta/components/meta'
import {Form} from '#/main/app/content/form/components/form'
import {FormFieldset} from '#/main/app/content/form/components/fieldset'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'

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

      if (field.linked) {
        sectionErrors = sectionErrors.concat(getSectionErrors(field.linked), errors)
      }
    }
  })

  return sectionErrors
}

const FormData = (props) => {
  const hLevel = props.level + (props.title ? 1 : 0)
  let hDisplay
  if (props.displayLevel) {
    hDisplay = props.displayLevel + (props.title ? 1 : 0)
  }

  const sections = createFormDefinition(props.sections, props.data)

  const primarySections = 1 === sections.length ? [sections[0]] : sections.filter(section => section.primary)
  const otherSections = 1 !== sections.length ? sections.filter(section => !section.primary) : []
  const openedSection = otherSections.find(section => section.defaultOpened)

  return (
    <Form
      id={props.id}
      className={props.className}
      embedded={props.embedded}
      disabled={props.disabled}
      level={props.level}
      displayLevel={props.displayLevel}
      title={props.title}
      errors={!isEmpty(props.errors)}
      validating={props.validating}
      pendingChanges={props.pendingChanges}
      alertExit={props.alertExit}
      save={props.save}
      cancel={props.cancel}
      lock={props.lock}
      getLock={props.getLock}
      unlock={props.unlock}
    >
      {props.meta &&
        <ContentMeta
          creator={get(props.data, 'meta.creator')}
          created={get(props.data, 'meta.created')}
          updated={get(props.data, 'meta.updated')}
        />
      }

      {primarySections.map(primarySection =>
        <div
          id={`${getSectionId(primarySection, props.id)}-section`}
          key={primarySection.id || toKey(primarySection.title)}
          className={classes('form-primary-section panel panel-default', primarySection.className)}
        >
          <Heading level={hLevel} displayed={false}>
            {primarySection.title}
          </Heading>

          <FormFieldset
            id={getSectionId(primarySection, props.id)}
            className="panel-body"
            disabled={props.disabled || primarySection.disabled}
            fields={primarySection.fields}
            data={props.data}
            errors={props.errors}
            validating={props.validating}
            updateProp={props.updateProp}
            setErrors={props.setErrors}
          >
            {primarySection.component}
            {!primarySection.component && primarySection.render && primarySection.render()}
          </FormFieldset>
        </div>
      )}

      {0 !== otherSections.length &&
        <FormSections
          level={hLevel}
          displayLevel={hDisplay}
          defaultOpened={openedSection ? (openedSection.id || toKey(openedSection.title)) : undefined}
        >
          {otherSections.map(section =>
            <FormSection
              id={`${getSectionId(section, props.id)}-section`}
              className={section.className}
              key={section.id || toKey(section.title)}
              icon={section.icon}
              title={section.title}
              subtitle={section.subtitle}
              errors={getSectionErrors(section.fields, props.errors)}
              validating={props.validating}
            >
              <FormFieldset
                id={getSectionId(section, props.id)}
                fill={true}
                className="panel-body"
                disabled={props.disabled || section.disabled}
                fields={section.fields}
                data={props.data}
                errors={props.errors}
                validating={props.validating}
                updateProp={props.updateProp}
                setErrors={props.setErrors}
              >
                {section.component}
                {!section.component && section.render && section.render()}
              </FormFieldset>
            </FormSection>
          )}
        </FormSections>
      }

      {props.children}
    </Form>
  )
}

FormData.propTypes = {
  id: T.string,
  /**
   * Is the form embed into another ?
   *
   * Permits to know if we use a <form> or a <fieldset> tag.
   */
  embedded: T.bool,
  level: T.number,
  displayLevel: T.number,
  title: T.string,
  className: T.string,
  disabled: T.bool,
  errors: T.object,
  validating: T.bool,
  pendingChanges: T.bool,
  /**
   * Alerts the user when leaving the form with unsaved changes
   */
  alertExit: T.bool,

  meta: T.bool,
  data: T.object,
  sections: T.arrayOf(T.shape(
    DataFormSectionTypes.propTypes
  )).isRequired,

  lock: T.shape({
    id: T.string.isRequired,
    className: T.string.isRequired
  }),

  getLock: T.func.isRequired,
  unlock: T.func.isRequired,

  /**
   * The save action of the form.
   */
  save: T.shape({
    type: T.string.isRequired,
    disabled: T.bool
    // todo find a way to document custom action type props
  }),

  /**
   * The cancel action of the form (if provided.
   */
  cancel: T.shape({
    type: T.string.isRequired,
    disabled: T.bool
    // todo find a way to document custom action type props
  }),
  setErrors: T.func.isRequired,
  updateProp: T.func.isRequired,
  children: T.node
}

FormData.defaultProps = {
  level: 2,
  data: {}
}

export {
  FormData
}
