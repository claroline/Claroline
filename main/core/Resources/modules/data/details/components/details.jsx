import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'
import merge from 'lodash/merge'

import {trans} from '#/main/core/translation'
import {toKey} from '#/main/core/scaffolding/text/utils'
import {Heading} from '#/main/core/layout/components/heading'
import {Sections, Section} from '#/main/core/layout/components/sections'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group'
import {getTypeOrDefault} from '#/main/core/data'
import {DataDetailsSection as DataDetailsSectionTypes} from '#/main/core/data/details/prop-types'
import {createDetailsDefinition} from '#/main/core/data/details/utils'

// todo there are big c/c from Form component but I don't know if we can do better

const DataDetailsField = props => {
  const typeDef = getTypeOrDefault(props.type)

  return (
    <div id={props.name} className={props.className}>
      {(!props.value && false !== props.value) &&
        <span className="data-details-empty">{trans('empty_value')}</span>
      }

      {(props.value || false === props.value)  && (typeDef.components.details ?
        React.createElement(typeDef.components.details, merge({}, props.options, {
          id: props.name,
          label: props.label,
          hideLabel: props.hideLabel,
          data: props.value // todo rename into `value` in implementations later
        }))
        :
        typeDef.render ? typeDef.render(props.value, props.options || {}) : props.value
      )}
    </div>
  )
}

DataDetailsField.propTypes = {
  value: T.any,
  name: T.string.isRequired,
  type: T.string,
  label: T.string.isRequired,
  hideLabel: T.bool,
  options: T.object,
  className: T.string
}

const DataDetailsGroup = props => {
  const typeDef = getTypeOrDefault(props.type)

  return (props.render ?
    <FormGroup
      id={props.name}
      label={props.label}
      hideLabel={props.hideLabel}
      help={props.help}
    >
      {props.render(props.data)}
    </FormGroup> :
    <FormGroup
      id={props.name}
      label={typeDef.meta && typeDef.meta.noLabel ? props.label : undefined}
      hideLabel={props.hideLabel}
      help={props.help}
    >
      <DataDetailsField
        {...props}
        value={props.calculated ? props.calculated(props.data) : get(props.data, props.name)}
      />
    </FormGroup>
  )
}

DataDetailsGroup.propTypes = {
  value: T.any,
  name: T.string.isRequired,
  type: T.string,
  label: T.string.isRequired,
  hideLabel: T.bool,
  options: T.object,
  help: T.oneOfType([T.string, T.arrayOf(T.string)]),
  data: T.object.isRequired, // the whole data object
  calculated: T.func,
  render: T.func
}

const DataDetails = props => {
  const hLevel = props.level + (props.title ? 1 : 0)
  let hDisplay
  if (props.displayLevel) {
    hDisplay = props.displayLevel + (props.title ? 1 : 0)
  }

  const sections = createDetailsDefinition(props.sections)

  const primarySections = 1 === sections.length ? [sections[0]] : sections.filter(section => section.primary)
  const otherSections = 1 !== sections.length ? sections.filter(section => !section.primary) : []
  const openedSection = otherSections.find(section => section.defaultOpened)

  return (
    <div className={classes('data-details', props.className)}>
      {props.title &&
        <Heading level={props.level} displayLevel={props.displayLevel}>
          {props.title}
        </Heading>
      }

      {primarySections.map(primarySection =>
        <div key={toKey(primarySection.title)} className="panel panel-default primary-section">
          <div className="panel-body">
            <Heading level={hLevel} displayed={false}>
              {primarySection.title}
            </Heading>

            {primarySection.fields.map(field =>
              <DataDetailsGroup
                {...field}
                key={field.name}
                data={props.data}
              />
            )}
          </div>
        </div>
      )}

      {0 !== otherSections.length &&
        <Sections
          level={hLevel}
          displayLevel={hDisplay}
          defaultOpened={openedSection ? openedSection.id : undefined}
        >
          {otherSections.map(section =>
            <Section
              key={toKey(section.title)}
              icon={section.icon}
              title={section.title}
            >
              {section.fields.map(field =>
                <DataDetailsGroup
                  {...field}
                  key={field.name}
                  data={props.data}
                />
              )}
            </Section>
          )}
        </Sections>
      }

      {props.children}
    </div>
  )
}

DataDetails.propTypes = {
  className: T.string,
  level: T.number,
  displayLevel: T.number,
  title: T.string,
  data: T.object,
  sections: T.arrayOf(T.shape(
    DataDetailsSectionTypes.propTypes
  )).isRequired,
  children: T.node
}

DataDetails.defaultProps = {
  level: 2,
  data: {}
}

export {
  DataDetails
}
