import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {toKey} from '#/main/core/scaffolding/text/utils'

import {Heading} from '#/main/core/layout/components/heading'
import {ContentMeta} from '#/main/app/content/meta/components/meta'
import {FormSections, FormSection} from '#/main/core/layout/form/components/form-sections'
import {SubSet} from '#/main/core/layout/form/components/fieldset/sub-set'
import {ToggleableSet} from '#/main/core/layout/form/components/fieldset/toggleable-set'

import {createFormDefinition} from '#/main/app/content/form/utils'
import {DataFormSection as DataFormSectionTypes} from '#/main/app/content/form/prop-types'
import {Form} from '#/main/app/content/form/components/form'
import {FormProp} from '#/main/app/content/form/components/prop'
import {FormGroup} from '#/main/app/content/form/components/group'

const AdvancedSection = props =>
  <ToggleableSet
    showText={props.showText}
    hideText={props.hideText}
  >
    {props.fields.map(field =>
      <FormProp
        key={field.name}
        {...field}
      />
    )}
  </ToggleableSet>

AdvancedSection.propTypes = {
  showText: T.string,
  hideText: T.string,
  fields: T.array.isRequired
}

class FormData extends Component {
  renderFields(fields) {
    let rendered = []

    fields.map(field => {
      if (field.render) {
        rendered.push(
          <FormGroup
            id={field.name}
            key={field.name}
            label={field.label}
            hideLabel={field.hideLabel}
            help={field.help}
          >
            {field.render(this.props.data)}
          </FormGroup>
        )
      } else {
        rendered.push(
          <FormProp
            {...field}
            key={field.name}
            value={field.calculated ? field.calculated(this.props.data) : get(this.props.data, field.name)}
            disabled={this.props.disabled || (typeof field.disabled === 'function' ? field.disabled(this.props.data) : field.disabled)}
            validating={this.props.validating}
            error={get(this.props.errors, field.name)}
            updateProp={this.props.updateProp}
            setErrors={this.props.setErrors}
          />
        )
      }

      if (field.linked && 0 !== field.linked.length) {
        rendered.push(
          <SubSet key={`${field.name}-subset`}>
            {this.renderFields(field.linked)}
          </SubSet>
        )
      }
    })

    return rendered
  }

  render() {
    const hLevel = this.props.level + (this.props.title ? 1 : 0)
    let hDisplay
    if (this.props.displayLevel) {
      hDisplay = this.props.displayLevel + (this.props.title ? 1 : 0)
    }

    const sections = createFormDefinition(this.props.sections, this.props.data)

    const primarySections = 1 === sections.length ? [sections[0]] : sections.filter(section => section.primary)
    const otherSections = 1 !== sections.length ? sections.filter(section => !section.primary) : []
    const openedSection = otherSections.find(section => section.defaultOpened)

    return (
      <Form
        className={this.props.className}
        embedded={this.props.embedded}
        disabled={this.props.disabled}
        level={this.props.level}
        displayLevel={this.props.displayLevel}
        title={this.props.title}
        errors={this.props.errors}
        validating={this.props.validating}
        pendingChanges={this.props.pendingChanges}
        save={this.props.save}
        cancel={this.props.cancel}
      >
        {this.props.meta &&
          <ContentMeta meta={get(this.props.data, 'meta')} />
        }

        {primarySections.map(primarySection =>
          <div key={toKey(primarySection.title)} className="form-primary-section panel panel-default">
            <fieldset className="panel-body">
              <Heading level={hLevel} displayed={false}>
                {primarySection.title}
              </Heading>

              {this.renderFields(primarySection.fields)}

              {primarySection.advanced &&
                <AdvancedSection {...primarySection.advanced} />
              }
            </fieldset>
          </div>
        )}

        {0 !== otherSections.length &&
          <FormSections
            level={hLevel}
            displayLevel={hDisplay}
            defaultOpened={openedSection ? openedSection.id : undefined}
          >
            {otherSections.map(section =>
              <FormSection
                key={toKey(section.title)}
                icon={section.icon}
                title={section.title}
                subtitle={section.subtitle}
                errors={this.props.errors}
                validating={this.props.validating}
              >
                {this.renderFields(section.fields)}

                {section.advanced &&
                  <AdvancedSection {...section.advanced} />
                }
              </FormSection>
            )}
          </FormSections>
        }

        {this.props.children}
      </Form>
    )
  }
}

FormData.propTypes = {
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

  meta: T.bool,
  data: T.object,
  sections: T.arrayOf(T.shape(
    DataFormSectionTypes.propTypes
  )).isRequired,
  setErrors: T.func.isRequired,
  updateProp: T.func.isRequired,
  children: T.node
}

FormData.defaultProps = {
  data: {}
}

export {
  FormData
}
